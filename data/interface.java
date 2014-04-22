class GameState
{
	Stack{} actionStacks; // associate array (cardName is index)
	Stack{} moneyStacks; // associate array (cardName is index)
	Stack{} pointStacks; // associate array (cardName is index)
	// curseStack is part of pointStacks (indexed 'curse')
	Player{} players; // associate array (player_id is index)
	String[] next_player_ids; // normal array (next player is always indexed 0, array ends at currentPlayer)
	CardName[] cardsPlayedInTurn;
	// cardsPlayedInTurn will almost always be blank when sent to ai/index.php,
	//     since the AI should usually send all cards played and bought, etc.
	//     during the turn within JSON of PlayerResponse.
	String currentPlayer; // player_id for the current player.
	String game_id; // randomly generated numerical ID of 20 chars long

	class Player
	{
		String player_id; // randomly generated numerical ID of 20 chars long
		Boolean human;
		CardName[] hand;
		CardName[] deck;
		// though AI should not have knowledge of this, but deck will always
		//     have the next 5 cards be indexed 0 to 4 in the JavaScript
		//     implementation.
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
	CardName name; // allowed characters are a to z and space
	String type; // allowed characters are a to z and dash
	// type is enum of 'treasure','victory','curse','action','action-attack'
	int cost;
	String value; // only used in 'treasure', 'victory', and 'curse' cards
	Deltas deltas;
	String desc; // when there is an icon, [2 COIN] and [SHIELD] are used

	class Deltas
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
		String action; // enum of 'play','buy','draw','shuffle'
		String object; // object of the action
		
	}
}