require 'test_helper'

class GroupControllerTest < ActionController::TestCase
  context "on get to show" do
    setup do
      @user = Factory.create(:user, :group_name => "Emerald City Sprints")
      get :show,  :group_name => 'emerald-city-sprints'
    end

    should_assign_to :user
    should "find the right user by group name" do
      assert_equal @user, assigns(:user)
    end
  end
end
