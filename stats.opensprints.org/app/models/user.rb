class User < ActiveRecord::Base
  include Clearance::User

  has_many :config_file
end
