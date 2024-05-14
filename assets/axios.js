export async function getAddress(data) {
  console.log(data);
  try {
    const response = await axios.get(data);
    return response.data.response;
  } catch (error) {
    console.error(error);
  }
}
