<?php 
    class Player {

        private $name;
        private $cards;

        
        public function __construct(string $name) {
            $this->name = $name;
            $this->cards = [];
        }


        /**
         * @return Card[] liste des cartes détenues par le joueur
        */
        public function getCards(): array {
            return $this->cards;
        }


        /**
         * @param Card[] $cards liste des cartes que le joueur possède.
        */ 
        public function setCards(array $cards) {
            $this->cards = $cards;
        }


        /**
         * @return string nom du joueur
        */        
        public function getName(): string {
            return $this->name;
        }


        /**
         * @return Card retourne la première carte du deck
        */             
        public function getFirstCard(): Card {
            return $this->cards[0];
        }


        /**
         * @param int $cardIndex index de la carte a récupérer par rapport à $this->cards
         * @return Card retourne n carte du deck
        */               
        public function getSpecificCard(int $cardIndex): Card {
            return array_slice($this->cards, $cardIndex, 1)[0];
        }

    }
?>