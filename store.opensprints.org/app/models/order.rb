class Order < ActiveRecord::Base
  module Totaling
    def total
      map(&:amount).sum
    end
  end

  before_create :generate_token
  before_save :update_line_items, :update_totals
  after_create :create_checkout_and_shippment, :create_tax_charge

  belongs_to :user
  has_many :state_events

  has_many :line_items,   :extend => Totaling, :dependent => :destroy, :attributes => true
  has_many :inventory_units

  has_many :payments,            :extend => Totaling
  has_many :creditcard_payments, :extend => Totaling

  has_one :checkout
  has_one :bill_address, :through => :checkout
  has_many :shipments, :dependent => :destroy

  has_many :adjustments,      :extend => Totaling, :order => :position
  has_many :charges,          :extend => Totaling, :order => :position
  has_many :shipping_charges, :extend => Totaling, :order => :position,
    :class_name => "Charge", :conditions => {:secondary_type => "ShippingCharge"}
  has_many :tax_charges,      :extend => Totaling, :order => :position,
    :class_name => "Charge", :conditions => {:secondary_type => "TaxCharge"}
  has_many :credits,          :extend => Totaling, :order => :position
  has_many :coupon_credits, :class_name => "Credit", :extend => Totaling, :conditions => {:adjustment_source_type => "Coupon"}, :order => :position

  accepts_nested_attributes_for :checkout
  
  def ship_address; shipment.address; end
  delegate :shipping_method, :to =>:shipment
  delegate :email, :to => :checkout
  delegate :ip_address, :to => :checkout
  delegate :special_instructions, :to => :checkout 
  
  validates_associated :line_items, :message => "are not valid"
  validates_numericality_of :item_total
  validates_numericality_of :total

  named_scope :by_number, lambda {|number| {:conditions => ["orders.number = ?", number]}}
  named_scope :between, lambda {|*dates| {:conditions => ["orders.created_at between :start and :stop", {:start => dates.first.to_date, :stop => dates.last.to_date}]}}
  named_scope :by_customer, lambda {|customer| {:include => :user, :conditions => ["users.email = ?", customer]}}
  named_scope :by_state, lambda {|state| {:conditions => ["state = ?", state]}}
  named_scope :checkout_complete, {:include => :checkout, :conditions => ["checkouts.completed_at IS NOT NULL"]}
  make_permalink :field => :number
  
  # attr_accessible is a nightmare with attachment_fu, so use attr_protected instead.
  attr_protected :charge_total, :item_total, :total, :user, :number, :state, :token

  def to_param  
    self.number if self.number
    generate_order_number unless self.number
    self.number.parameterize.to_s.upcase
  end

  def checkout_complete
    checkout.completed_at
  end
  # order state machine (see http://github.com/pluginaweek/state_machine/tree/master for details)
  state_machine :initial => 'in_progress' do    
    after_transition :to => 'in_progress', :do => lambda {|order| order.update_attribute(:checkout_complete, false)}
    after_transition :to => 'new', :do => :complete_order
    after_transition :to => 'canceled', :do => :cancel_order
    after_transition :to => 'returned', :do => :restock_inventory
    after_transition :to => 'resumed', :do => :restore_state 

    event :complete do
      transition :to => 'new', :from => 'in_progress'
    end
    event :cancel do
      transition :to => 'canceled', :if => :allow_cancel?
    end
    event :return do
      transition :to => 'returned', :from => 'shipped'
    end
    event :resume do 
      transition :to => 'resumed', :from => 'canceled', :if => :allow_resume?
    end    
    event :pay do
      transition :to => 'paid', :if => :allow_pay?
    end
    event :ship do
      transition :to => 'shipped', :from  => 'paid'
    end
  end
  
  def restore_state
    # pop the resume event so we can see what the event before that was
    state_events.pop if state_events.last.name == "resume"
    update_attribute("state", state_events.last.previous_state)
  end

  def allow_cancel?
    self.state != 'canceled'
  end
  
  def allow_resume?
    # we shouldn't allow resume for legacy orders b/c we lack the information necessary to restore to a previous state
    return false if state_events.empty? || state_events.last.previous_state.nil?
    true
  end
  
  def allow_pay?
    checkout_complete
  end
  
  def add_variant(variant, quantity=1)
    current_item = contains?(variant)
    if current_item
      current_item.increment_quantity unless quantity > 1
      current_item.quantity = (current_item.quantity + quantity) if quantity > 1
      current_item.save
    else
      current_item = LineItem.new(:quantity => quantity)
      current_item.variant = variant
      current_item.price   = variant.price
      self.line_items << current_item
    end
    
    # populate line_items attributes for additional_fields entries
    # that have populate => [:line_item]
    Variant.additional_fields.select{|f| !f[:populate].nil? && f[:populate].include?(:line_item) }.each do |field| 
      value = ""
      
      if field[:only].nil? || field[:only].include?(:variant)
        value = variant.send(field[:name].gsub(" ", "_").downcase)
      elsif field[:only].include?(:product)
        value = variant.product.send(field[:name].gsub(" ", "_").downcase)
      end
      current_item.update_attribute(field[:name].gsub(" ", "_").downcase, value)
    end
  end

  def generate_order_number                
    record = true
    while record
      random = "R#{Array.new(9){rand(9)}.join}"                                        
      record = Order.find(:first, :conditions => ["number = ?", random])
    end          
    self.number = random
  end          
    
  # convenience method since many stores will not allow user to create multiple shipments
  def shipment
    shipments.last
  end
  
  def contains?(variant)
    line_items.select { |line_item| line_item.variant == variant }.first
  end

  def grant_access?(token=nil)
    return true if token && token == self.token
    return false unless current_user_session = UserSession.find   
    return current_user_session.user == self.user
  end
  def mark_shipped
    inventory_units.each do |inventory_unit|
      inventory_unit.ship!
    end
  end
      
  # collection of available shipping countries
  def shipping_countries
    ShippingMethod.all.collect { |method| method.zone.country_list }.flatten.uniq.sort_by {|item| item.send 'name'}
  end
  
  def shipping_methods
    return [] unless ship_address
    ShippingMethod.all.select { |method| method.zone.include?(ship_address) && method.available?(self) }
  end
   
  def payment_total
    payments.reload.total
  end

  def ship_total
    shipping_charges.reload.total
  end

  def tax_total
    tax_charges.reload.total
  end

  def credit_total
    credits.reload.total.abs
  end

  def charge_total
    charges.reload.total
  end

  def create_tax_charge
    if tax_charges.empty?
      tax_charges.create({
          :order => self,
          :description => I18n.t(:tax),
          :adjustment_source => self,
        })
    end
  end

  def update_totals
    self.item_total       = self.line_items.total

    charges.reload.each(&:update_amount)
    self.adjustment_total = self.charge_total - self.credit_total

    self.total            = self.item_total   + self.adjustment_total
  end

  def update_totals!
    update_totals
    save!
  end

  private
  
  def complete_order
    checkout.update_attribute(:completed_at, Time.now)
    InventoryUnit.sell_units(self)
    save_result = save!
    if email
      OrderMailer.deliver_confirm(self)
    end
    save_result
  end

  def cancel_order
    restock_inventory
    OrderMailer.deliver_cancel(self)
  end
  
  def restock_inventory
    inventory_units.each do |inventory_unit|
      inventory_unit.restock! if inventory_unit.can_restock?
    end
  end
 
  def update_line_items
    to_wipe = self.line_items.select {|li| 0 == li.quantity || li.quantity.nil? }
    LineItem.destroy(to_wipe)
    self.line_items -= to_wipe      # important: remove defunct items, avoid a reload
  end
  
  def generate_token
    self.token = Authlogic::Random.friendly_token
  end
  
  def create_checkout_and_shippment
    self.shipments << Shipment.create(:order => self)
    self.checkout ||= Checkout.create(:order => self)
  end
end
