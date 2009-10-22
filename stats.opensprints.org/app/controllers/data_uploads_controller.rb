class DataUploadsController < ApplicationController
  skip_before_filter :verify_authenticity_token
  def new
    @data_upload = DataUpload.new
  end

  def create
    unless current_user
      authenticate_with_http_basic { |u, p| sign_in(User.authenticate(u,p)) }
    end
    params[:data_upload].merge!({:user_id => current_user.id})
    @data_upload = DataUpload.create(params[:data_upload])
    respond_to do |format|
      format.html { redirect_to :action => :index, :user => current_user }
      format.xml { render :xml => @data_upload.to_xml }
    end
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
