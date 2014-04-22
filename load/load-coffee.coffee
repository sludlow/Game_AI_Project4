waterfall = (stream) ->
	if stream.length is 0
		return
	else
		stream[0].call
			stream: stream
			step: 0
			proceed: (step_length=1) ->
				@step += step_length
				@stream[@step].call this
			redo: () ->
				@stream[@step].call this
			go: (step_num) ->
				@step = step_num
				@stream[@step].call this

choose = () ->
	if arguments.length is 1 and arguments[0].length
		arguments[0][Math.floor(arguments[0].length*Math.random())]
	else
		arguments[Math.floor(arguments.length*Math.random())]


cardholder = (stacks=[], options) ->
	if options
		{width,height,size,orientation}=options
	rv = {}
	rv.attributes = []
	if width
		rv.attributes.push "data-width='#{width}'"
	if height
		rv.attributes.push "data-height='#{height}'"
	if size
		rv.attributes.push "data-size='#{size}'"
	rv.classNames = ['cardholder']
	if orientation
		rv.classNames.push orientation
	rv.stacks = stacks
	rv.addCard = (cardName) ->
		rv.cardNames.push cardName
	rv.html = () ->
		tds = _.map(@stacks,(a)->"<td data-card='#{a.cardName}' data-amount='#{a.amount}'></td>").join('');
		"
			<table class='#{@classNames.join(' ')}' style='display:none;' #{@attributes.join(' ')}>
				<tr>
					#{tds}
				</tr>
			</table>
		"
	rv

GameState =
	actionStacks: {}
	moneyStacks: {}
	pointStacks: {}
	players: {}
	cardsPlayedInRound: []
	currentPlayer: undefined
	game_id: undefined

stack_draw = (stack) ->
	stack.amount--
	return stack.cardName

toStacks = (cardNames) ->
	_.map(cardNames,(cardName)->{cardName:cardName,amount:1})

shuffle = () ->
	rv = []
	orig = [].slice.call if arguments.length is 1 and arguments[0].length then arguments[0] else arguments
	while orig.length
		rv.push orig.splice(Math.floor(Math.random()*orig.length),1)[0]
	return rv

reset_discard = (player) ->
	player.deck = shuffle player.discard
	player.discard = []
	true

make_id = () ->
	randstr = () ->
		"#{Math.random()}".substr(2)
	rv = ''
	while rv.length<20
		rv+=randstr()
	rv.substr(0,20)

waterfall [
	() -> # initial settings
		@num_of_players = 2;
		@proceed();
	() -> # obtain card information
		$.getJSON('./data/cards.json').done (data)=>
			@cards = data
			@proceed();
	() -> # initialize GameState
		# generate actionStacks
		actionStacks = []
		while actionStacks.length<10
			card = choose @cards
			if ['action','action-attack','action-reaction'].some((a)->a is card.type)
				if actionStacks.every((a)->a.cardName isnt card.name)
					actionStacks.push
						cardName:card.name
						amount:10
		GameState.actionStacks={};
		actionStacks.forEach (a)->GameState.actionStacks[a.cardName]=a;
		
		# generate moneyStacks
		GameState.moneyStacks=
			copper:
				cardName: 'copper'
				amount: 60
			silver:
				cardName: 'silver'
				amount: 40
			gold:
				cardName: 'gold'
				amount: 30
		
		# generate pointStacks
		GameState.pointStacks=
			estate:
				cardName: 'estate'
				amount: if @num_of_players is 2 then 8 else 12
			duchy:
				cardName: 'duchy'
				amount: if @num_of_players is 2 then 8 else 12
			province:
				cardName: 'province'
				amount: if @num_of_players is 2 then 8 else 12
			curse:
				cardName: 'curse'
				amount: 10*(@num_of_players-1)
		
		# generate players
		first_player_id = -1
		for i in [1..@num_of_players]
			player_id = make_id()
			if first_player_id is -1
				first_player_id = player_id
			GameState.players[player_id] =
				player_id: player_id
				human: (i is 1)
				hand: []
				deck: []
				discard: []
			for i in [1..7]
				GameState.players[player_id].discard.push stack_draw(GameState.moneyStacks.copper)
			for i in [1..3]
				GameState.players[player_id].discard.push 'estate' # estate stack already adjusted per amount of players
			reset_discard GameState.players[player_id]
			for i in [1..5]
				GameState.players[player_id].hand.push GameState.players[player_id].deck.shift()
		GameState.currentPlayer = first_player_id
		
		# set game_id
		GameState.game_id = make_id();
		
		
		@proceed();
	() -> # setup livequery for cardholder
		card_width = {}
		card_height = {}
		card_width.normal = 182;
		card_height.normal = 291;
		card_width.thumb = 112;
		card_height.thumb = Math.round(card_width.thumb/card_width.normal*card_height.normal);
		card_width.tiny = 50;
		card_height.tiny = Math.round(card_width.tiny/card_width.normal*card_height.normal);
		$('table.cardholder td[data-card]').livequery ()->
			width_attr = " width='#{card_width[$(@).closest('table.cardholder').attr('data-size')]}'"
			html = ""
			html += "<div class='card_count_wrapper'>"
			html += "<center class='card_count_giftbox'>#{$(@).attr('data-amount')}</center>"
			html += "</div>"
			html += "<img#{width_attr} class='card' src='./images/cards/"
			html += $(@).attr('data-card').toLowerCase().replace(`/[^a-zA-Z0-9]/g`,'')
			html += ".jpg' alt='#{$(@).attr('data-card').toUpperCase()}' />"
			$(@).html html
		$('table.cardholder').livequery ()->
			if +$(@).attr('data-width')>0
				cw=card_width[$(@).attr('data-size')];
				cw+=10; # for extra spacing in between.
				$(@).add($(@).find('td')).attr('width',cw)
				$(@).add($(@).find('td')).css('width',"#{cw}px");
				$(@).add($(@).find('td')).css('max-width',"#{cw}px");
			if +$(@).attr('data-height')>0
				ch=card_height[$(@).attr('data-size')];
				ch+=10; # for extra spacing in between.
				$(@).add($(@).find('td')).attr('height',ch);
				$(@).add($(@).find('td')).css('height',"#{ch}px");
				$(@).add($(@).find('td')).css('max-height',"#{ch}px");
			$(@).attr('width',cw*+$(@).attr('data-width'));
			$(@).css('width',"#{cw*+$(@).attr('data-width')}px");
			$(@).css('max-width',"#{cw*+$(@).attr('data-width')}px");
			$(@).attr('height',ch*+$(@).attr('data-height'));
			$(@).css('height',"#{ch*+$(@).attr('data-height')}px");
			$(@).css('max-height',"#{ch*+$(@).attr('data-height')}px");
			$(@).css('display','block');
		@proceed()
	() -> # setup card overlays on hover
		overlay = $ "
			<table id='card_overlay' style='display:none;'>
				<tr>
					<td valign='middle'>
						<center>
							<a class='info button'>Info</a><br />
							<a class='action button'>Play</a><br />
							<a class='action button'>Draw</a><br />
							<a class='action button'>Buy</a><br />
							<a class='action button'>Take</a><br />
							<a class='action button'>Trash</a>
						</center>
					</td>
				</tr>
			</table>
		"
		$(document.body).append overlay
		$(document.body).on 'mouseenter','img.card',()->
			{left,top}=$(@).offset()
			$('#card_overlay').css('position','absolute');
			$('#card_overlay').css('left',"#{left}px");
			$('#card_overlay').css('top',"#{top}px");
			$('#card_overlay').add('#card_overlay td').css('width',"#{$(@).width()}px");
			$('#card_overlay').add('#card_overlay td').css('height',"#{$(@).height()}px");
			$('#card_overlay').css('display','block');
		$(document.body).on 'mouseleave','#card_overlay',()->
			$('#card_overlay').css('display','none');
		@proceed()
	() -> # setup sections
		# table center
		$(document.body).append "
			<table id='wrapper' width='100%' height='100%'>
				<tr>
					<td valign='middle'>
						<center id='giftbox'></center>
					</td>
				</tr>
			</table>
		"
		# top container for cards in actionStacks
		$(document.body).append "
			<div id='top_wrapper'><center id='top_giftbox'></center></div>
		"
		# left container for cards in moneyStacks and pointStacks
		$(document.body).append "
			<table id='left_bottom_wrapper'>
				<tr>
					<td id='left_bottom_giftbox' valign='middle'></td>
				</tr>
			</table>
		"
		# bottom container for cards in hand
		$(document.body).append "
			<div id='bottom_wrapper'><center id='bottom_giftbox'></center></div>
		"
		@proceed()
	() -> # setup game board
		actionStacks = new cardholder GameState.actionStacks,
			width: 10
			height: 1
			size: 'thumb'
		$('#top_giftbox').html actionStacks.html()
		
		moneyStacks = new cardholder GameState.moneyStacks,
			width: 3
			height: 1
			size: 'tiny'
		pointStacks = new cardholder GameState.pointStacks,
			width: 4
			height: 1
			size: 'tiny'
		$('#left_bottom_giftbox').html "#{moneyStacks.html()}<br />#{pointStacks.html()}"
		
		hand = new cardholder toStacks(GameState.players[GameState.currentPlayer].hand),
			height: 1
			width: 5
			size: 'thumb'
		$('#bottom_giftbox').html hand.html()
]
