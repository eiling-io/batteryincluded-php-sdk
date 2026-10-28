# frozen_string_literal: true

# jekyll-polyglot builds the site once per language, just-the-docs takes some texts and paths from the site config.
# This localizes them for every language besides the default one.
module Localize
  def self.prefix(site)
    "#{site.baseurl}/#{site.active_lang}"
  end
end

Jekyll::Hooks.register :site, :pre_render do |site|
  # keep the values of the default language, the hook runs once per language on the same config
  site.config['localized_defaults'] ||= site.config.slice(*site.config.fetch('localized', {}).values.flat_map(&:keys).uniq)
  site.config.merge!(site.config['localized_defaults'])
  site.config.merge!(site.config.fetch('localized', {}).fetch(site.active_lang, {}))

  # <html lang> of just-the-docs
  site.config['lang'] = site.active_lang
end

# the pages load /assets/js/just-the-docs.js, which loads the search index of the default language
Jekyll::Hooks.register [:pages, :documents], :post_render do |page|
  site = page.site
  next if site.active_lang == site.default_lang

  if page.url == '/assets/js/just-the-docs.js'
    page.output = page.output.sub("'#{site.baseurl}/assets/js/search-data.json'", "'#{Localize.prefix(site)}/assets/js/search-data.json'")
  elsif page.output_ext == '.html'
    page.output = page.output.sub(%(src="#{site.baseurl}/assets/js/just-the-docs.js"), %(src="#{Localize.prefix(site)}/assets/js/just-the-docs.js"))
  end
end
