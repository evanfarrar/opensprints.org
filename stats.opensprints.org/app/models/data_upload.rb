class DataUpload < ActiveRecord::Base
  belongs_to :user
  has_attached_file :database
end
