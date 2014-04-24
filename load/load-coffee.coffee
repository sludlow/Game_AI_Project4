debug = true

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
		{width,height,size,orientation,hidecount}=options
	rv = {}
	rv.attributes = []
	if width
		rv.attributes.push "data-width='#{width}'"
	if height
		rv.attributes.push "data-height='#{height}'"
	if size
		rv.attributes.push "data-size='#{size}'"
	if hidecount
		rv.attributes.push "data-hidecount='true'"
	rv.classNames = ['cardholder']
	if orientation
		rv.classNames.push orientation
	rv.stacks = stacks
	rv.addCard = (cardName) ->
		rv.cardNames.push cardName
	rv.html = () ->
		tds = _.map(@stacks,(a)->"<td data-card='#{a.cardName}' data-amount='#{a.amount}' style='#{if a.amount is 0 then 'opacity:0.25' else ''}'></td>").join('');
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
	next_player_ids: []
	cardsPlayedInTurn: []
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

myTimeout = (func) -> setTimeout func,300

waterfall [
	() -> # initial settings
		@num_of_players = 2;
		@proceed()
	() -> # obtain card information
		$.getJSON('./data/cards.json').done (data)=>
			@cards = data
			@proceed()
	() -> # initialize GameState
		# generate actionStacks
		actionStacks = []
		while actionStacks.length<10
			card = choose @cards
			if ['action','action-attack','action-reaction'].some((a)->a is card.type) or card.name is 'gardens'
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
			GameState.next_player_ids.push player_id
			GameState.players[player_id] =
				player_id: player_id
				human: (i is 1)
				hand: []
				deck: []
				discard: []
			# enforce 4-3 split
			for i in [1..4]
				GameState.players[player_id].hand.push stack_draw(GameState.moneyStacks.copper)
			for i in [1..3]
				GameState.players[player_id].deck.push stack_draw(GameState.moneyStacks.copper)
			for i in [1..1]
				GameState.players[player_id].hand.push 'estate' # estate stack already adjusted per amount of players
			for i in [1..2]
				GameState.players[player_id].deck.push 'estate' # estate stack already adjusted per amount of players
			GameState.players[player_id].hand = shuffle GameState.players[player_id].hand
			GameState.players[player_id].deck = shuffle GameState.players[player_id].deck
		GameState.currentPlayer = GameState.next_player_ids.shift()
		GameState.next_player_ids.push GameState.currentPlayer
		
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
			if !$(@).closest('table.cardholder').attr('data-hidecount')
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
		# left bottom container for cards in moneyStacks and pointStacks
		$(document.body).append "
			<table id='left_bottom_wrapper'>
				<tr>
					<td id='left_bottom_giftbox' valign='middle'></td>
				</tr>
			</table>
		"
		# right bottom container for top card in discard pile
		$(document.body).append "
			<table id='right_bottom_wrapper'>
				<tr>
					<td id='right_bottom_giftbox' valign='middle'></td>
				</tr>
			</table>
		"
		# bottom container for cards in hand
		$(document.body).append "
			<div id='bottom_wrapper'><center id='bottom_giftbox'></center></div>
		"
		@proceed()
	() -> # setup game board
		@display_gamestate = () ->
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
		
			if GameState.cardsPlayedInTurn.length > 0
				cardsInPlay = new cardholder toStacks(GameState.cardsPlayedInTurn),
					height: 1
					width: GameState.cardsPlayedInTurn.length
					size: 'thumb'
					hidecount: true
				$('#giftbox').html cardsInPlay.html()
			else
				$('#giftbox').html ''
			
			if GameState.players[GameState.currentPlayer].hand.length > 0
				hand = new cardholder toStacks(GameState.players[GameState.currentPlayer].hand),
					height: 1
					width: GameState.players[GameState.currentPlayer].hand.length
					size: 'thumb'
					hidecount: true
				$('#bottom_giftbox').html hand.html()
			else
				$('#bottom_giftbox').html ''
			
			right_bottom_giftbox_htmls = []
			deck_display =
				cardName: 'back'
				amount: GameState.players[GameState.currentPlayer].deck.length
			deck = new cardholder [deck_display],
				height: 1
				width: 1
				size: 'tiny'
			right_bottom_giftbox_htmls.push "<td>#{deck.html()}</td>"
			discard_display =
				cardName: GameState.players[GameState.currentPlayer].discard[0]
				amount: GameState.players[GameState.currentPlayer].discard.length
			discard = new cardholder [discard_display],
				height: 1
				width: 1
				size: 'tiny'
			right_bottom_giftbox_htmls.push "<td>#{discard.html()}</td>"
			$('#right_bottom_giftbox').html "<table><tr>#{right_bottom_giftbox_htmls.join('')}</tr></table>"
		@display_gamestate()
		@proceed()
	() -> # obtain moves from AI
		$.ajax
			url: (if debug then './testai.php' else './ai/index.php')
			data: {_:JSON.stringify GameState}
			dataType: 'text'
			type: 'POST'
			traditional: true
			success: (text)=>
				myTimeout ()=>
					alert "Player #{GameState.currentPlayer} choose moves:\n#{text}"
					data = JSON.parse text
					@playerResponse = data
					@proceed()
	() -> # process moves
		for move in @playerResponse.moves
			switch move.action
				when 'buy'
					if move.object of GameState.actionStacks
						bought_card = stack_draw GameState.actionStacks[move.object]
					else if move.object of GameState.moneyStacks
						bought_card = stack_draw GameState.moneyStacks[move.object]
					else if move.object of GameState.pointStacks
						bought_card = stack_draw GameState.pointStacks[move.object]
					GameState.players[GameState.currentPlayer].discard.unshift bought_card
				when 'play'
					for cardName,cardNum in GameState.players[GameState.currentPlayer].hand
						if cardName is move.object
							GameState.players[GameState.currentPlayer].hand.splice(cardNum,1)
							GameState.cardsPlayedInTurn.push cardName
							break
				when 'shuffle'
					GameState.players[GameState.currentPlayer].deck = move.updateDeck
					GameState.players[GameState.currentPlayer].discard = []
				when 'draw'
					cardFromDeck = GameState.players[GameState.currentPlayer].deck.shift()
					GameState.players[GameState.currentPlayer].hand.push cardFromDeck
				when 'trash'
					for cardName,cardNum in GameState.players[GameState.currentPlayer].hand
						if cardName is move.object
							GameState.players[GameState.currentPlayer].hand.splice(cardNum,1)
							break
		@proceed()
	() -> # finalize turn
		@display_gamestate();
		myTimeout ()=>
			if not confirm "Player #{GameState.currentPlayer}'s turn has ended.\n\nClick Cancel to end game."
				return
			for cardPlayed in GameState.cardsPlayedInTurn
				GameState.players[GameState.currentPlayer].discard.unshift cardPlayed
			GameState.cardsPlayedInTurn=[]
			for cardInHand in GameState.players[GameState.currentPlayer].hand
				GameState.players[GameState.currentPlayer].discard.unshift cardInHand
			GameState.players[GameState.currentPlayer].hand=[]
			for i in [1..5]
				if GameState.players[GameState.currentPlayer].deck.length is 0
					reset_discard GameState.players[GameState.currentPlayer]
				cardFromDeck = GameState.players[GameState.currentPlayer].deck.shift()
				GameState.players[GameState.currentPlayer].hand.push cardFromDeck
			
			GameState.currentPlayer = GameState.next_player_ids.shift()
			GameState.next_player_ids.push GameState.currentPlayer
			@display_gamestate()
			@proceed(-2)
]
