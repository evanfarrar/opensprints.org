class EventsController < ApplicationController
  def show
    @data_upload =  DataUpload.find(params[:data_upload_id])
    @user = @data_upload.user
    @event = @data_upload.get_event(params[:id])
  end
end
