class User < ActiveRecord::Base
  include Clearance::User

  validates_presence_of :group_name
  attr_accessible :group_name

  has_many :config_files
  has_many :data_uploads

  def before_save
    self.group_name_for_url = self.group_name.parameterize
  end

end
