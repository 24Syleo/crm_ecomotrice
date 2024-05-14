import { getAddress } from "./axios.js";

function getGpsPoint() {
  const address = document.getElementById("customer_address");
  const lat = document.getElementById("customer_lat");
  const lng = document.getElementById("customer_lng");

  if (address === undefined || lat === undefined || lng === undefined) return;
  if (!address) return;

  address.addEventListener("change", async (evt) => {
    console.log(evt.target.value);

    const result = await getAddress(
      "https://api.geocodify.com/v2/geocode?api_key=AvwBCftqa1ndf10fHUDiBzjwgFrNdE72&q=" +
        evt.target.value
    );

    console.log(result.bbox[0], result.bbox[1]);

    lat.value = result.bbox[0];
    lng.value = result.bbox[1];
  });
}

getGpsPoint();
