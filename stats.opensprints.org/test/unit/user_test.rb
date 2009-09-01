require 'test_helper'

class UserTest < ActiveSupport::TestCase
  # Replace this with your real tests.
  context "User" do
    should_have_many :config_file
  end
end
