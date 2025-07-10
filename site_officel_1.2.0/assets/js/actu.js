// Temps entre chaque rafraîchissement en millisecondes (exemple : 10 secondes)
const refreshInterval = 6000;

function shouldRefresh() {
  const active = document.activeElement;
  // Vérifie si l'élément actif est un input text ou un textarea
  return !(active && 
           (active.tagName === 'TEXTAREA' || 
            (active.tagName === 'INPUT' && active.type === 'text')));
}

setInterval(() => {
  if (shouldRefresh()) {
    location.reload();
  }
}, refreshInterval);
