# Be sure to restart your server when you modify this file.

# Your secret key for verifying cookie session data integrity.
# If you change this key, all old sessions will become invalid!
# Make sure the secret is at least 30 characters and all random, 
# no regular words or you'll be exposed to dictionary attacks.
ActionController::Base.session = {
  :key         => '_store.opensprints.org_session',
  :secret      => 'c846c479beb2a238d6be8894bf63512077090dffff58aa7345e368f9c02c38a3b3d56974cb7832da16bc7b72458120d1c588244a53ac44f572561fd7a5edb2d3'
}

# Use the database for sessions instead of the cookie-based default,
# which shouldn't be used to store highly confidential information
# (create the session table with "rake db:sessions:create")
# ActionController::Base.session_store = :active_record_store