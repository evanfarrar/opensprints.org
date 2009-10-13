class DataUploadsController < ApplicationController
  def new
    @data_upload = DataUpload.new
  end

  def create
    params[:data_upload].merge!({:user_id => current_user.id})
    @data_upload = DataUpload.create(params[:data_upload])
    redirect_to :action => :index
  end

  def index
    @data_uploads = DataUpload.find(:all,:conditions => {:user_id => current_user.id})
  end
end
