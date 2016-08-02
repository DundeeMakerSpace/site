import L from 'leaflet';

export default function() {
	const config = TerminallyPixelated;
    const lat = config.location.latitude;
    const lon = config.location.longitude;

    let map = document.getElementById('footer-map');
    map = L.map(map, {
        scrollWheelZoom: false,
        // zoomControl: false
    }).setView([lat, lon], 16);

    const CartoDB_DarkMatter = L.tileLayer('http://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
        subdomains: 'abcd',
        maxZoom: 19
    });
    CartoDB_DarkMatter.addTo(map);

    const icon = L.divIcon({
        className: 'footer-map__marker',
        iconSize: [12,12],
        iconAnchor: [6,6],
    });

    const popupMessage = config.location.address + '<br /><br /><a href="https://maps.google.com?saddr=Current+Location&daddr=' + config.location.latitude + ',' + config.location.longitude + '">Get Directions</a>';

    const marker = L.marker([lat, lon], {icon: icon})
    	.addTo(map)
    	.bindPopup(popupMessage);
};
