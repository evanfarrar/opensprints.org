# COPYRIGHT: 2007 Brent Beardsley (brentbeardsley@gmail.com)
# LICENSE: MIT
module BlueprintcssHelper
  # Helper for blueprint css framework at http://code.google.com/p/blueprintcss
  # Options
  #   :plugins takes an array of plugins to load by name like :plugins => [:buttons, 'fancy-type']
  #   :compressed => true or false (default: true) on whether or not to use compressed css files
  #   :show_grid => true or false (default: false) on whether or not to draw grid on container background
  def blueprintcss(options = {})
    options[:compressed] = true unless options.has_key?(:compressed)

    screen_css = options[:compressed] ? 'compressed/screen' : 'screen'
    print_css = options[:compressed] ? 'compressed/print' : 'print'
    ie_css = 'lib/ie'
    if options[:compressed] && File.exist?(File.join(@staticmatic.site_dir, 'stylesheets', 'blueprint', 'compressed', 'ie.css'))
      ie_css = 'compressed/ie'
    end
    outp = blueprintcss_stylesheet(screen_css, :media => 'screen, projection') + "\n"
    outp << blueprintcss_stylesheet(print_css, :media => 'print') + "\n"
    outp << "<!--[if IE]>\n"
    outp << "  " + blueprintcss_stylesheet(ie_css, :media => 'screen, projection') + "\n"
    outp << "<![endif]-->\n"

    if options[:plugins]
      options[:plugins].to_a.each do |plugin|
        plugin_name = plugin
        if options[:compressed]
          if File.exist?(File.join(@staticmatic.site_dir, 'stylesheets', 'blueprint', 'plugins', plugin.to_s, "#{plugin}-compressed.css"))
            plugin_name = "#{plugin}-compressed"
          end
        end
        outp << blueprintcss_stylesheet("plugins/#{plugin}/#{plugin_name}", :media => 'screen, projection') + "\n"
      end
    end

    if options[:show_grid]
      outp << tag(:style, :type => 'text/css') do 
        show_output = "\n/*<![CDATA[*/\n"
        show_output << "  .container { background: url('#{current_page_relative_path}stylesheets/blueprint/lib/grid.png'); }\n"
        show_output << "/*]]>*/\n"
        show_output
      end
    end

    outp
  end

  def blueprintcss_stylesheet(name, options = {})
    href = "#{current_page_relative_path}stylesheets/blueprint/#{name}.css"
    tag(:link, {:href => href, :rel => 'stylesheet', :type => 'text/css'}.merge(options)) 
  end
  private :blueprintcss_stylesheet
end