class ConfigFile < ActiveRecord::Base
  has_attached_file :config
  belongs_to :user

  def load_yaml
    YAML.load_file(config.path)
  end
end
