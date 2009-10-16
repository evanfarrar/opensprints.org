class User < ActiveRecord::Base
  include Clearance::User

  validates_presence_of :group_name
  attr_accessible :group_name

  has_many :config_files
  has_many :data_uploads

  def before_save
    self.group_name_for_url = self.group_name.parameterize
  end

  def tournaments
    set_sequel_database
    Tournament.all
  end

  def get_event(id)
    set_sequel_database
    Tournament[id]
  end

  def set_sequel_database
    db = Sequel.sqlite(data_uploads.last.database.path)
    Category.db = db
    Categorization.db = db
    RaceParticipation.db = db
    Race.db = db
    Racer.db = db
    TournamentParticipation.db = db
    Tournament.db = db
  end
end
