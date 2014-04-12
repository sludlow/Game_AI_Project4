class GameState
{
	Player{} players; // associate array
	Stack[10] actionStacks; // ten stacks of action cards per table
	Stack[3] moneyStacks;
	Stack[3] pointStacks;
	Stack curseStack;
	Stack playStack;
	String currentPlayer;
	String game_id; // randomly generated numerical ID of 20 characters long

	class Player
	{
		String player_id; // randomly generated numerical ID of 20 characters long
		Boolean human; // true or false
		Stack[] hand;
		Stack[] deck;
		Stack[] discard;
	}

	class Stack
	{
		Card[] cards;
	}

	class Card
	{
		String name;
		String type;
		int cost;
		Delta deltas;
		String desc;
	}

	class Delta
	{
		int actions=0;
		int buys=0;
		int cards=0;
		int coins=0;
	}
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