class Admin::ShipmentsController < Admin::BaseController
  before_filter :load_data, :except => :country_changed

  resource_controller
  belongs_to :order
  
  create do
    wants.html { redirect_to edit_object_url }
    failure.wants.html { redirect_to new_object_url }
  end

  edit.before do # copy into instance variable before editing
    @shipment.special_instructions = @order.special_instructions
  end

  update.after do # copy back to order if instructions are enabled
    if Spree::Config[:shipping_instructions]
      @order.special_instructions = object_params[:special_instructions] 
      @order.save
    end
  end

  update do
    wants.html { redirect_to edit_object_url }
  end 
  
#  def country_changed
#  end
  
  private
  def build_object
    @object ||= end_of_association_chain.send parent? ? :build : :new, object_params
    @object.address = Address.new(:country_id => Spree::Config[:default_country_id]) unless @object.address
    @object
  end
  
  def load_data 
    load_object
    @selected_country_id = params[:shipment_presenter][:address_country_id].to_i if params.has_key?('shipment_presenter')
    @selected_country_id ||= @order.bill_address.country_id unless @order.nil? || @order.bill_address.nil?  
    @selected_country_id ||= Spree::Config[:default_country_id]
 
    @states = State.find_all_by_country_id(@selected_country_id, :order => 'name')  
    @countries = @order.shipping_countries
    @countries = [Country.find(Spree::Config[:default_country_id])] if @countries.empty?
  end

end
