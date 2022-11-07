<?php

    class Card {

        private $sign;
        private $value;


        public function __construct(string $sign, string $value) {
            $this->sign = $sign;
            $this->value = $value;
        }


        /**
         * Retour la valeur d'une carte.
         * @return string valeur d'une carte, à savoir ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'Valet', 'Dame', 'Roi', 'As'];
         */
        public function getValue(): string {
            return $this->value;
        }

        
        /**
         * Retour le signe d'une carte.
         * @return string signe d'une carte, à savoir ['Coeur', 'Trèfle', 'Pique', 'Carreau']
         */
        public function getSign(): string {
            return $this->sign;
        }

    }

?>