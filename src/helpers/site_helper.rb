module SiteHelper
  def navigation_link(label,url,selected)
    selected==label ? "<span>#{label}</span>": link(label, url)
  end

end
