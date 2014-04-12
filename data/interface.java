class GameState
{
	Player{} players; // associate array
	Stack{} actionStacks; // associate array action stacks (cardName is index)
	Stack{} moneyStacks; // associate array money stacks (cardName is index)
	Stack{} pointStacks; // associate array point stacks (cardName is index)
	// curseStack is part of pointStacks (indexed 'curse')
	CardName[] playStack;
	String currentPlayer;
	String game_id; // randomly generated numerical ID of 20 characters long

	class Player
	{
		String player_id; // randomly generated numerical ID of 20 characters long
		Boolean human; // true or false
		CardName[] hand;
		CardName[] deck;
		CardName[] discard;
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
		int actions=0;
		int buys=0;
		int cards=0;
		int coins=0;
	}

	class CardName is String;
}

class PlayerResponse
{
	PlayerMove[] moves;
	
	class PlayerMove
	{
		String action; // enum of 'play', 'buy'
		String cardName; // object of the action
	}
}