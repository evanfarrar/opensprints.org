require 'test_helper'

class UserTest < ActiveSupport::TestCase
  # Replace this with your real tests.
  context "User" do
    should_have_many :config_files
    should_have_many :data_uploads
    should "have a name for their group" do
      u = User.new
      u.group_name = "Salt City Sprints"
      assert_equal "Salt City Sprints",u.group_name
    end
  end
end
