class ConfigFilesController < ApplicationController
  def show
    @config_file = ConfigFile.find(params[:id])
  end

  def new
    @config_file = ConfigFile.new
  end

  def create
    params[:config_file].merge!({:user_id => current_user.id})
    @config_file = ConfigFile.create(params[:config_file])
    redirect_to :action => :index, :user => current_user
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
