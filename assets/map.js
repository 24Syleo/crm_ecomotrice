function initMap() {
  const inputLon = document.getElementById("lon");
  const inputLat = document.getElementById("lat");

  if (inputLon === undefined || inputLat === undefined) return;
  if (!inputLon || !inputLat) return;

  const lon = inputLon.value;
  const lat = inputLat.value;

  const map = L.map("map").setView([lon, lat], 16);
  const marker = L.marker([lon, lat]).addTo(map);

  L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
      '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);
}

initMap();
