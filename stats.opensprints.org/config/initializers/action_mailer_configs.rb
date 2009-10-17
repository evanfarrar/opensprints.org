ActionMailer::Base.smtp_settings = {
  :address => 'mail.opensprints.com',
  :port => 587,
  :domain => 'opensprints.com',
  :authentication => :login,
  :user_name => 'system@opensprints.com',
  :password => 'password'
}

