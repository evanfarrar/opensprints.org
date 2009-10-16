class EventController < ApplicationController
  def show
    @user = User.find_by_group_name_for_url(params[:group_name])
    @event = @user.get_event(params[:id])
  end
end
