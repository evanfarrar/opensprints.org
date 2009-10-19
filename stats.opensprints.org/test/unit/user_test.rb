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

    should_validate_presence_of :group_name
    should_allow_mass_assignment_of :group_name

    should "parameterize group name before save" do
      u = Factory.build(:user)
      u.group_name = "Salt City Sprints"
      u.save!
      assert_equal "salt-city-sprints",u.group_name_for_url
    end

    should "delegate all data upload methods to the most recent" do
      @user = User.create
      data_upload1 = mock()
      data_upload1.stubs(:tournaments).returns("first")
      data_upload2 = mock()
      data_upload2.stubs(:tournaments).returns("last")
      @user.expects(:data_uploads).returns([data_upload1, data_upload2])
      assert_equal "last", @user.tournaments
    end
  end
end
