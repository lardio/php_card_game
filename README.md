# Simulation du jeux de cartes "la bataille" en PHP.
* Chaque joueur recoit 26 cartes aléatoires.
* A chaque tour, chaque joueur retourne une carte, la plus forte l'emporte.
* Le joueur gagant place sa carte jouée ainsi que la carte de l'adversaire à la fin de son deck.
* En cas d'égalité, chaque joueur place une carte face cachée sur la carte précédente, puis joue une deuxième carte face ouverte qui déterminera le vainqueur. En cas d'égalité on répète le process. 
* Si un joueur ne peut pas joueur plus de deux carte en cas d'égalité, alors la carte suivant celle qui a emmenée à l'égalité sera celle qui décidera du vainqueur.
* Si un joueur tombe sur une égalité et que c'est sa dernière carte alors il a perdu.