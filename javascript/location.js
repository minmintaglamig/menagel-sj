document.addEventListener("DOMContentLoaded", function () {
    const citySelect = document.querySelector("select[name='city']");
    const barangaySelect = document.getElementById("barangay");

    const barangays = {
        "Biñan City": ["Biñan", "Bungahan", "Santo Tomas (Calabuso)", "Canlalay", "Casile", "De La Paz", "Ganado", "San Francisco (Halang)", "Langkiwa", "Loma", "Malaban", "Malamig", "Mampalasan (Mamplasan)", "Platero", "Poblacion", "Santo Niño", "San Antonio", "San Jose", "San Vicente", "Soro-Soro", "Santo Domingo", "Timbao", "Tubigan", "Zapote"],
        "Cabuyao City": ["Baclaran", "Banay-Banay", "Banlic", "Bigaa", "Butong", "Casile", "Gulod", "Mamatid", "Marinig", "Niugan", "Pittland", "Pulo", "Sala", "San Isidro", "Diezmo", "Barangay Uno (Pob.)", "Barangay Dos (Pob.)", "Barangay Tres (Pob.)"],
        "Calamba City": ["Bagong Kalsada", "Bañadero", "Banlic", "Barandal", "Batino", "Bubuyan", "Bucal", "Bunggo", "Canlubang", "Mayapa", "Paciano Rizal", "San Cristobal", "Real", "Makiling", "Tulo", "Turbina", "Sirang-Lupa", "Uwisan", "Looc", "Mapagong", "Sampiruhan", "Palingon", "San Juan", "Parian", "Lingga", "Lawa", "San Jose"],
        "San Pablo City": ["Atisan", "Bagong Bayan II-A", "Bagong Pook VI-C", "Barangay I-A", "Barangay I-B", "Barangay II-A", "Barangay II-B", "Barangay II-C", "Barangay II-D", "Barangay II-E", "Barangay II-F", "Barangay III-A", "Barangay III-B", "Barangay III-C", "Barangay III-D", "Barangay III-E", "Barangay III-F", "Barangay IV-A", "Barangay IV-B", "Barangay IV-C", "Barangay V-A", "Barangay V-B", "Barangay V-C", "Barangay V-D", "Barangay VI-A", "Barangay VI-B", "Barangay VI-D", "Barangay VI-E", "Barangay VII-A", "Barangay VII-B", "Barangay VII-C", "Barangay VII-D", "Barangay VII-E", "Bautista", "Concepcion", "Del Remedio", "Dolores", "San Antonio 1", "San Antonio 2", "San Bartolome", "San Buenaventura", "San Crispin", "San Cristobal", "San Diego", "San Francisco", "San Gabriel", "San Gregorio", "San Ignacio", "San Isidro", "San Joaquin", "San Jose", "San Juan", "San Lorenzo", "San Lucas 1", "San Lucas 2", "San Marcos", "San Mateo", "San Miguel", "San Nicolas", "San Pedro", "San Rafael", "San Roque", "San Vicente", "Santa Ana", "Santa Catalina", "Santa Cruz", "Santa Filomena", "Santa Isabel", "Santa Maria", "Santa Maria Magdalena", "Santa Monica", "Santa Veronica", "Santiago I", "Santiago II", "Santisimo Rosario", "Santo Angel", "Santo Cristo", "Santo Niño", "Soledad"],
        "San Pedro City": ["Bagong Silang", "Calendola", "Chrysanthemum", "Cuyab", "Estrella", "Fatima", "G.S.I.S.", "Landayan", "Langgam", "Laram", "Maharlika", "Magsaysay", "Narra", "Nueva", "Pacita 1", "Pacita 2", "Poblacion", "Riverside", "Rosario", "Sampaguita Village", "San Antonio", "San Lorenzo Ruiz", "San Roque", "San Vicente", "Santo Niño", "United Bayanihan"],
        "Santa Rosa City": ["Aplaya", "Balibago", "Caingin", "Dila", "Dita", "Don Jose", "Ibaba", "Kanluran (Poblacion Uno)", "Labas", "Macabling", "Malitlit", "Malusak (Poblacion Dos)", "Market Area (Poblacion Tres)", "Pooc (Pook)", "Pulong Santa Cruz", "Santo Domingo", "Sinalhan", "Tagapo"]
    };

    citySelect.addEventListener("change", function () {
        const selectedCity = this.value;
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>'; 

        if (selectedCity && barangays[selectedCity]) {
            barangays[selectedCity].forEach(barangay => {
                const option = document.createElement("option");
                option.value = barangay;
                option.textContent = barangay;
                barangaySelect.appendChild(option);
            });
        }
    });
});