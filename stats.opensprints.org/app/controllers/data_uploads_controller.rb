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
    if params[:user]
      @user = User.find(params[:user])
      @data_uploads = DataUpload.find(:all,:conditions => {:user_id => params[:user]})
    else
      @data_uploads = DataUpload.find(:all)
    end
  end

  def show
    @data_upload = DataUpload.find(params[:id])
  end
end
