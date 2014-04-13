class GameState
{
	Player{} players; // associate array (player_id is index)
	Stack{} actionStacks; // associate array action stacks (cardName is index)
	Stack{} moneyStacks; // associate array money stacks (cardName is index)
	Stack{} pointStacks; // associate array point stacks (cardName is index)
	// curseStack is part of pointStacks (indexed 'curse')
	CardName[] cardsPlayedInRound;
	// cardsPlayedInRound will always be blank when sent to PHP,
	//     since the AI will send all cards played and bought, etc.
	//     in PlayerResponse
	String currentPlayer; // player_id for the current player.
	String game_id; // randomly generated numerical ID of 20 chars long

	class Player
	{
		String player_id; // randomly generated numerical ID of 20 chars long
		Boolean human; // true or false
		CardName[] hand;
		CardName[] deck; // next 5 cards will always be indexed 0 to 4
		CardName[] discard; // last discarded card will always be indexed 0
	}

	class Stack
	{
		CardName cardName;
		int amount;
	}
	
	class CardName is String;
}

class Card
{
	CardName name;
	String type;
	int cost;
	Delta deltas;
	String desc;

	class Delta
	{
		int actions=0; // optional, default is 0
		int buys=0;    // optional, default is 0
		int cards=0;   // optional, default is 0
		int coins=0;   // optional, default is 0
	}

	class CardName is String;
}

class PlayerResponse
{
	PlayerMove[] moves;
	// moves are ordered in the way that they should be processed.
	
	class PlayerMove
	{
		String action; // enum of 'play', 'buy'
		String cardName; // object of the action
	}
}