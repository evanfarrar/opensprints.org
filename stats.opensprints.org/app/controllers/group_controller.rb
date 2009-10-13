class GroupController < ApplicationController
  def show
    @user = User.find_by_group_name_for_url(params[:group_name])
    render :text => params[:group_name]
  end
end
