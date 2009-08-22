ENV["RAILS_ENV"] = "test"
require File.expand_path(File.dirname(__FILE__) + "/../config/environment")
require 'test_help'

class ActiveSupport::TestCase
  self.use_transactional_fixtures = true
  self.use_instantiated_fixtures  = false
end

I18n.locale = "en-US"

ActionController::TestCase.class_eval do
  # special overload methods for "global"/nested params
  [ :get, :post, :put, :delete ].each do |overloaded_method|
    define_method overloaded_method do |*args|
      action,params,extras = *args
      super action, params || {}, *extras unless @params
      super action, @params.merge( params || {} ), *extras if @params
    end
  end
end  

def setup
  super
  @params = {}
end

class TestCouponCalc
  def self.calculate_discount(checkout)    
    0.99
  end
end

class Zone
  def self.global
    find_by_name("GlobalZone") || Factory(:global_zone)
  end
end
def create_complete_order
  @zone = Zone.global
  @order = Factory(:order)
  3.times do
    variant = Factory(:product).variants.first
    Factory(:line_item, :variant => variant, :order => @order)
  end

  @shipping_method = Factory(:shipping_method)
  @checkout = @order.checkout
  @shipment = @order.shipment
  @order.shipment.shipping_method = @shipping_method
  @order.shipment.address = Factory(:address)
  @checkout.bill_address = Factory(:address)
  unless @zone.include?(@order.shipment.address)
    ZoneMember.create(:zone => Zone.global, :zoneable => @order.shipment.address.country)
    @zone.reload
  end

  @checkout.save
  @shipment.save
  @order.save
  @order.reload
end