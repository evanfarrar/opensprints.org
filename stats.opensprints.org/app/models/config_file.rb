class ConfigFile < ActiveRecord::Base
  has_attached_file :config
  belongs_to :user
end
