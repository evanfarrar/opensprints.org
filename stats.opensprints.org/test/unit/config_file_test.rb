require 'test_helper'

class ConfigFileTest < ActiveSupport::TestCase
  context "ConfigFile" do
    should_belong_to :user
    should_have_attached_file :config
  end
end
