class User < ActiveRecord::Base
  include Clearance::User

  has_many :config_files
  has_many :data_uploads
end
