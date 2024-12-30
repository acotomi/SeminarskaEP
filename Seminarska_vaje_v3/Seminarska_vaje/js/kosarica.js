document.addEventListener('DOMContentLoaded', function() {
    // Dodajanje v košarico
    const dodajGumbi = document.querySelectorAll('.dodaj-v-kosaro');
    dodajGumbi.forEach(gumb => {
        gumb.addEventListener('click', function() {
            const izdelekId = this.dataset.izdelekId;
            fetch('../api/kosarica_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    artikel_id: izdelekId,
                    kolicina: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Izdelek dodan v košarico!');
                } else {
                    alert(data.error || 'Napaka pri dodajanju v košarico.');
                }
            })
            .catch(error => {
                console.error('Napaka:', error);
                alert('Prišlo je do napake pri dodajanju v košarico.');
            });
        });
    });

    // Spreminjanje količine v košarici
    const kolicineInputi = document.querySelectorAll('.kolicina-input');
    kolicineInputi.forEach(input => {
        input.addEventListener('change', function() {
            const kosaricaId = this.dataset.kosaricaId;
            const novaKolicina = parseInt(this.value);
            const vrstica = this.closest('tr');
            const cenaElement = vrstica.querySelector('.cena-na-kos');
            const skupajElement = vrstica.querySelector('.skupaj-cena');
            
            // Pridobi ceno na kos iz data atributa
            const cenaNaKos = parseFloat(cenaElement.dataset.cena);
            
            // Izračunaj novo skupno ceno za vrstico
            const skupajCena = cenaNaKos * novaKolicina;
            
            // Posodobi prikaz skupne cene za vrstico
            skupajElement.textContent = skupajCena.toFixed(2) + ' €';
            skupajElement.dataset.skupaj = skupajCena;

            // Posodobi skupno ceno košarice
            posodobiSkupnoCeno();

            // Pošlji spremembo na strežnik
            fetch('../api/kosarica_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    kosarica_id: kosaricaId,
                    kolicina: novaKolicina
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Napaka pri posodabljanju količine.');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Napaka:', error);
                alert('Prišlo je do napake pri posodabljanju količine.');
                location.reload();
            });
        });
    });

    // Funkcija za posodobitev skupne cene košarice
    function posodobiSkupnoCeno() {
        const vseSkupajCene = document.querySelectorAll('.skupaj-cena');
        let skupnaCena = 0;
        
        vseSkupajCene.forEach(element => {
            skupnaCena += parseFloat(element.dataset.skupaj || 0);
        });

        const skupnaCenaElement = document.querySelector('.skupna-cena-kosarice');
        if (skupnaCenaElement) {
            skupnaCenaElement.textContent = skupnaCena.toFixed(2) + ' €';
            skupnaCenaElement.dataset.skupna = skupnaCena;
        }
    }

    // Odstranjevanje iz košarice
    const odstraniGumbi = document.querySelectorAll('.odstrani-izdelek');
    odstraniGumbi.forEach(gumb => {
        gumb.addEventListener('click', function() {
            if (confirm('Ali ste prepričani, da želite odstraniti ta izdelek iz košarice?')) {
                const kosaricaId = this.dataset.kosaricaId;
                
                fetch('../api/kosarica_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        kosarica_id: kosaricaId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Napaka pri odstranjevanju izdelka.');
                    }
                })
                .catch(error => {
                    console.error('Napaka:', error);
                    alert('Prišlo je do napake pri odstranjevanju izdelka.');
                });
            }
        });
    });

    // Oddaja naročila
    const oddajNarociloGumb = document.getElementById('oddaj-narocilo');
    if (oddajNarociloGumb) {
        oddajNarociloGumb.addEventListener('click', function() {
            if (confirm('Ali ste prepričani, da želite oddati naročilo?')) {
                fetch('../api/kosarica_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'checkout'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Naročilo je bilo uspešno oddano!');
                        location.reload();
                    } else {
                        alert(data.error || 'Napaka pri oddaji naročila.');
                    }
                })
                .catch(error => {
                    console.error('Napaka:', error);
                    alert('Prišlo je do napake pri oddaji naročila.');
                });
            }
        });
    }
});
