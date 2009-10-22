class ConfigFilesController < ApplicationController
  skip_before_filter :verify_authenticity_token

  def show
    @config_file = ConfigFile.find(params[:id])
  end

  def new
    @config_file = ConfigFile.new
  end

  def create
    unless current_user
      authenticate_with_http_basic { |u, p| sign_in(User.authenticate(u,p)) }
    end
    params[:config_file].merge!({:user_id => current_user.id})
    @config_file = ConfigFile.create(params[:config_file])
    respond_to do |format|
      format.html { redirect_to :action => :index, :user => current_user }
      format.xml { render :xml => @config_file.to_xml }
    end
  end


  def index
    if params[:user]
      @user = User.find(params[:user])
      @config_files = ConfigFile.find(:all,:conditions => {:user_id => params[:user]})
    else
      @config_files = ConfigFile.find(:all)
    end
  end
end
