less_pp = [
	{s:`/@import \(font\) "/g`,r:"@import (css) \"#{location.protocol}//fonts.googleapis.com/css?family="}
]

$.get('less/style.less?'+Math.random()).always (data) ->
	if typeof data is 'string'
		less_pp.forEach (sr) ->
			data = data.replace(sr.s,sr.r);
		parser = new less.Parser
		parser.parse data, (err,tree) ->
			if not tree
				console.log "Error compiling less/style.less: #{JSON.stringify err}"
			else
				style = document.createElement('style')
				style.innerHTML = tree.toCSS()
				$(document.head).append(style)
	else
		console.log "less/style.less not found."
