class DataUpload < ActiveRecord::Base
  belongs_to :user
  has_attached_file :database
  validates_presence_of :user

  def tournaments
    set_sequel_database
    Tournament.all
  end

  def get_event(id)
    set_sequel_database
    Tournament[id]
  end

  def set_sequel_database
    db = Sequel.sqlite(database.path)
    Category.db = db
    Categorization.db = db
    RaceParticipation.db = db
    Race.db = db
    Racer.db = db
    TournamentParticipation.db = db
    Tournament.db = db
  end

end
