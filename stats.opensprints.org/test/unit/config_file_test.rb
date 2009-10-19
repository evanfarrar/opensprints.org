require 'test_helper'

class ConfigFileTest < ActiveSupport::TestCase
  context "ConfigFile" do
    should_belong_to :user
    should_have_attached_file :config
    setup do
      @config = ConfigFile.new
      @config.config.stubs(:path).returns("test/fixtures/opensprints_conf.yml")
    end

    should "convert its attachment to a ruby object" do
      assert_equal({"window_width"=>"1024", "menu_background_image"=>"media/background.png", "background_image"=>nil, "window_height"=>"600", "title"=>"Open Sprints", "track"=>"progress_bars", "race_distance"=>200, "units"=>"standard", "locale"=>"it", "sensor"=>{"device"=>"/dev/ttyUSB0", "type"=>"basic_message_arduino"}, "background_color"=>"\"#bec0c2\"...\"#86888b\"", "bikes"=>["red", "blue"], "roller_circumference"=>1.0}, @config.load_yaml)
    end
  end
end
