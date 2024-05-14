const lon = document.getElementById("lon").value;
const lat = document.getElementById("lat").value;

console.log(lon, lat);

const map = L.map("map").setView([lon, lat], 16);
var marker = L.marker([lon, lat]).addTo(map);

L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
  maxZoom: 19,
  attribution:
    '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
}).addTo(map);
