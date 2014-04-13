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


cardholder = (cardNames=[], options) ->
	if options
		{width,height,thumb,orientation}=options
	rv = {}
	rv.attributes = []
	if width
		rv.attributes.push "data-width='#{width}'"
	if height
		rv.attributes.push "data-height='#{height}'"
	if thumb
		rv.attributes.push "data-thumb='#{thumb}'"
	rv.classNames = ['cardholder']
	if orientation
		rv.classNames.push orientation
	rv.cardNames = cardNames
	rv.addCard = (cardName) ->
		rv.cardNames.push cardName
	rv.html = () ->
		lis = _.map(@cardNames,(a)->"<li data-card='#{a}'></li>").join('');
		"
			<table class='#{@classNames.join(' ')}' style='display:none;' #{@attributes.join(' ')}>
				<tr>
					<td valign='middle'>
						<center>
							<ul>
								#{lis}
							</ul>
						</center>
					</td>
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
		for i in [1..@num_of_players]
			player_id = make_id()
			GameState.players[player_id] =
				player_id: player_id
				human: (i is 1)
				hand: []
				deck: []
				discard: []
			for i in [1..7]
				GameState.players[player_id].discard.push stack_draw(GameState.moneyStacks.copper)
			for i in [1..3]
				GameState.players[player_id].discard.push 'estate' # estate already adjusted per amount of players
			reset_discard GameState.players[player_id]
			for i in [1..5]
				GameState.players[player_id].hand.push GameState.players[player_id].deck.shift()
			if i is 1
				GameState.currentPlayer = player_id;
		
		# set game_id
		GameState.game_id = make_id();
		
		@proceed();
	() -> # setup livequery for cardholder
		normal_card_width = 182;
		normal_card_height = 291;
		thumb_card_width = 112;
		thumb_card_height = Math.round(thumb_card_width/normal_card_width*normal_card_height);
		$('table.cardholder li').livequery ()->
			if $(@).closest('table.cardholder').attr('data-thumb') is 'true'
				width_attr = " width='#{thumb_card_width}'"
			else
				width_attr = " width='#{normal_card_width}'"
			html = ""
			html += "<img#{width_attr} class='card' src='./images/cards/"
			html += $(@).attr('data-card').toLowerCase().replace(`/[^a-zA-Z0-9]/g`,'')
			html += ".jpg' alt='#{$(@).attr('data-card').toUpperCase()}' />"
			$(@).html html
		$('table.cardholder').livequery ()->
			if +$(@).attr('data-width')>0
				card_width=if $(@).attr('data-thumb') is 'true' then thumb_card_width else normal_card_width;
				card_width+=10; # for extra spacing in between.
				$(@).add($(@).find('td')).attr('width',card_width*+$(@).attr('data-width'))
				$(@).add($(@).find('td')).css('width',"#{card_width*+$(@).attr('data-width')}px");
				$(@).add($(@).find('td')).css('max-width',"#{card_width*+$(@).attr('data-width')}px");
			if +$(@).attr('data-height')>0
				card_height=if $(@).attr('data-thumb') is 'true' then thumb_card_height else normal_card_height;
				card_height+=10; # for extra spacing in between.
				$(@).add($(@).find('td')).attr('height',card_height*+$(@).attr('data-height'));
				$(@).add($(@).find('td')).css('height',"#{card_height*+$(@).attr('data-height')}px");
				$(@).add($(@).find('td')).css('max-height',"#{card_height*+$(@).attr('data-height')}px");
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
		# bottom container for cards in hand
		$(document.body).append "
			<div id='bottom_wrapper'><center id='bottom_giftbox'></center></div>
		"
		@proceed()
	() -> # draw 10 cards for the table
		@tableCardNames = []
		while @tableCardNames.length<10
			card = choose @cards
			if ['action','action-attack','action-reaction'].some((a)->a is card.type)
				if @tableCardNames.every((a)->a isnt card.name)
					@tableCardNames.push card.name
		@handCardNames = []
		while @handCardNames.length<5
			card = choose @cards
			if ['action','action-attack','action-reaction'].some((a)->a is card.type)
				if @handCardNames.every((a)->a isnt card.name)
					@handCardNames.push card.name
		@proceed()
	() -> # setup game board
		table = new cardholder @tableCardNames,
			width: 10
			height: 1
			thumb: true
		$('#giftbox').html table.html()
		
		hand = new cardholder @handCardNames,
			height: 1
			width: 5
			thumb: true
		$('#bottom_giftbox').html hand.html()
]
