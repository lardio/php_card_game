<?php 
    require_once('./classes/Card.php');

    class Game {
        private $playerOne;
        private $playerTwo;
        private $winner;
        private $cards;
        private $rounds = 0;

        private const CARDSIGNS = ['Coeur', 'Trèfle', 'Pique', 'Carreau']; // signes des cartes, inutile pour le jeu actuel mais défini si changement règles
        private const CARDSET = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'Valet', 'Dame', 'Roi', 'As']; // définition de la hiérarchie des cartes

        private $logFileName = 'game_logs.txt';
        private $logFile;


        public function __construct(Player $playerOne, Player $playerTwo) {
            $this->logFile = fopen("$this->logFileName", "w");

            $this->playerOne = $playerOne;
            $this->playerTwo = $playerTwo;

            $this->createCards(); // création du jeux complet
            shuffle($this->cards); // mélange des cartes
            $this->playerOne->setCards(array_slice($this->cards, 0, count($this->cards) / 2)); // attribution des cartes pour le joueur 1
            $this->playerTwo->setCards(array_slice($this->cards, count($this->cards) / 2)); // attribution des cartes pour le joueur 2

            $this->startGame();
        }



        /**
         * Initie le jeux de cartes complet pour la partie.
         */
        private function createCards(): void {
            $this->cards = [];
            foreach(self::CARDSIGNS as $cardSign) {
                foreach(self::CARDSET as $cardValue) {
                    array_push($this->cards, new Card($cardSign, $cardValue));
                }
            }
        }



        /**
         * Déclenche le début d'une partie. Joue en boucle les batailles jusqu'a qu'un vainqueur soit désigné.
         */
        public function startGame(): void {
            print_r("Début de la partie \n");

            while(!isset($this->winner)) {
                $this->rounds++;
                fwrite($this->logFile, "####################### \n");
                fwrite($this->logFile, "Round numéro => {$this->rounds}.\n");
                $this->battle();
                if(!isset($this->winner)) $this->controlPlayersCardCapacity();
            }
        }



        /**
         * Défini le comportement d'une bataille entre deux joueurs.
         * Chaque joueur joue la première carte de son deck, le vainqueur est celui avec la carte la plus élevée.
         * 
         * En cas d'égalité :
         * * les joueurs posent chacun une carte face cachée, puis une deuxième avec lequelles on controle le gagnant. En cas de nouvelle égalité on répète le processus jusqu'a qu'un gagnant soit désigné.
         * * Si un joueur ne peut pas jouer deux cartes additionnelles (une face cachée et une deuxième face ouverte), alors la désignation du vainqueur se fait par rapport à la valeur de la carte normalement face cachée. En cas d'égalité le joueur avec le plus de carte remporte la partie.
         * * Si un joueur ne peut jouer aucune autre carte, il perd la partie.
         */
        private function battle(): void {
            $resultBattle = $this->defineHigherCard($this->playerOne->getFirstCard()->getValue(), $this->playerTwo->getFirstCard()->getValue());
            if($resultBattle === 1) { // victoire p1
                $this->applyBattleConsequences($this->playerOne, $this->playerTwo, 1);
            } else if($resultBattle === 2) { // victoire 2
                $this->applyBattleConsequences($this->playerTwo, $this->playerOne, 1);
            } else { // égalité

                //par défaut on retourne les cartes qui ont déclenchées l'égalité, on les recouvres d'une carte face cachée et on ajoute un carte qui déterminera le gagant
                //le gagant gagne les 3+2*<nombre_dégalité -1> cartes qui ont été jouées par l'adversaire
                $egalityRoundsPlayed = 0;
                do {
                    // si un des joueurs ne peut pas jouer au moins une carte additionnelle il perd
                    if(!$this->controlPlayersCardCapacity(2 + 2 * $egalityRoundsPlayed)) break; 

                    // si un joueur peut joueur seulement une carte additionnelle (carte normalement face cachée) alors on prend cette carte comme la carte qui déterminera le vainqueur, sinon on joue avec scénario normal (+2 cartes par rapport a la derniere ayant entrainée l'égalité).
                    $indexCardPlayed = min([
                        $this->getPlayerCardCapacity($this->playerOne, $egalityRoundsPlayed), 
                        $this->getPlayerCardCapacity($this->playerTwo, $egalityRoundsPlayed)
                    ]);

                    $resultBattle = $this->defineHigherCard(
                        $this->playerOne->getSpecificCard($indexCardPlayed - 1)->getValue(), 
                        $this->playerTwo->getSpecificCard($indexCardPlayed - 1)->getValue()
                    );

                    if ($resultBattle === 1) { $this->applyBattleConsequences($this->playerOne, $this->playerTwo, $indexCardPlayed);
                    } else if ($resultBattle === 2) { $this->applyBattleConsequences($this->playerTwo, $this->playerOne, $indexCardPlayed);
                    }
                    $egalityRoundsPlayed++;

                } while ($resultBattle === 3); // tant qu'on a une égalité on rejoue un round supplémentaire sauf si un joueur a plus assez de cartes

            }
        }


        
        /**
         * Défini le rapport de force entre deux valeurs de cartes.
         * Se réfère avec le nombre le plus élevé de l'index de la carte (référence tableau $this->cardSet)
         * @param Card $cardOne carte jouée par le joueur 1
         * @param Card $cardTwo carte jouée par le joueur 2
         * @return int
         */
        private function defineHigherCard(string $cardOne, string $cardTwo): int {
            $logText = "{$this->playerOne->getName()} : {$cardOne} VS {$this->playerTwo->getName()} : {$cardTwo} \n";
            fwrite($this->logFile, $logText);

            if(array_search($cardOne, self::CARDSET) > array_search($cardTwo, self::CARDSET)) { // player1 gagne
                return 1;
            } else if (array_search($cardOne, self::CARDSET) < array_search($cardTwo, self::CARDSET)) { // player2 gagne
                return 2;
            }
            return 3; // égalité
        }



        /**
         * Applique les actions définies selon les règles de la bataille sur les joueurs à la fin d'une manche :
         * * Le joueur qui a perdu perd ses cartes joués qui iront dans la main du vainqueur.
         * * Les cartes joués par le vainqueur vont à la fin de son deck avec les cartes récupérés du perdant.
         * @param Player $winner joueur ayant remporté la manche
         * @param Player $looser joueur ayant perdu la manche
         * @param int $numberCardsPlayed nombre de cartes jouées dans la manche
         */
        private function applyBattleConsequences(Player $winner, Player $looser, int $numberCardsPlayed): void {
            $cardsToSetToWinner = array_slice( $looser->getCards(), 0, $numberCardsPlayed); // récupèration des cartes du perdant à passer au gagant
            $looser->setCards( array_slice($looser->getCards(), $numberCardsPlayed, count($looser->getCards())) ); // suppression des cartes du perdant
            $cardsForWinner = array_merge(array_slice($winner->getCards(), 0, $numberCardsPlayed), $cardsToSetToWinner); // récupération des cartes a ajouter a la fin du deck du gagant (cartes jouées + celles du perdant)
            $winner->setCards( array_merge(array_slice($winner->getCards(), $numberCardsPlayed, count($winner->getCards()) ), $cardsForWinner)); // ajout des cartes au deck du gagant
        }



        /**
         * Défini si un joueur a assez de cartes pour jouer la scénario normal après une égalité.
         * Si une égalité arrive, chaque joueur pose une carte face cachée et pose une carte suivante qui déterminera le vainqueur de la manche
         * @param Player $player joueur sur lequel on controle le nombre de cartes
         * @param int $egalityRoundsPlayed nombre de tour d'égalité successif
         * @return int nombre de cartes que le joueur peut jouer pour le tour d'égalité actuel.
         */
        private function getPlayerCardCapacity(Player $player, int $egalityRoundsPlayed = 0): int {
            $numberCards = count($player->getCards());
            if( $numberCards >= 3 + 2 * $egalityRoundsPlayed ) { // carte face cachée + carte ouverte
                return 3 + 2 * $egalityRoundsPlayed;
            } else if ( $numberCards >= 2 + 2 * $egalityRoundsPlayed ) { // que carte face cachée
                return 2 + 2 * $egalityRoundsPlayed;
            }
            return 1 + 2 * $egalityRoundsPlayed;
        }



        /**
         * Vérifie que les joueurs ont le nombre de cartes suffisant pour jouer la prochaine manche.
         * Si un des joueurs a pas assez de carte, l'autre joueur est déclaré vainqueur.
         * @param int $minCardNeed nombre de cartes minimum que les joueurs doivent avoir.
         * @return bool true si les joueurs ont assez de cartes, sinon un vainqueur est désigné.
         */
        private function controlPlayersCardCapacity(int $minCardNeed = 1): bool {
            $players = [$this->playerOne, $this->playerTwo];
            $iteration = 0;
            foreach($players as $player) {
                $numberCards = count($player->getCards());
                fwrite($this->logFile, "{$player->getName()} - {$numberCards} cartes \n");
                if ($numberCards < $minCardNeed ) {
                    unset($players[$iteration]);
                    $this->endGame(array_values($players)[0]);
                    break;
                }
                $iteration++;
            }
            return !isset($this->winner) ? true : false;
        }



        /**
         * Met fin à une partie en déclarat un joueur vainqueur.
         * @param Player $winner
         */
        private function endGame(Player $winner): void {
            print_r("Le joueur {$winner->getName()} a gagné la partie !\n");
            print_r("Il y'a eu en tout {$this->rounds} manches de jouées.\n");
            print_r("Les logs de la partie sont accessibles dans le fichier {$this->logFileName}\n");
            fwrite($this->logFile, "Victoire de {$winner->getName()} !\n");
            fclose($this->logFile);
            $this->winner = $winner;
        }

    }
?>