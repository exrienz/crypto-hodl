import requests
from bs4 import BeautifulSoup
import sys

# Check if the correct number of arguments is provided
if len(sys.argv) != 3:
    print("Usage: python tp2txt.py <hodl_id> <host_address>")
    sys.exit(1)

hodl_id = sys.argv[1]
host_address = sys.argv[2]

# Construct the URL
url = f'{host_address}/index.php?hodl_id={hodl_id}'

try:
    # Send a GET request to the URL
    response = requests.get(url)
    response.raise_for_status()  # Raise an error for bad responses (4xx or 5xx)

    # Parse the response content
    soup = BeautifulSoup(response.content, 'html.parser')

    # Initialize a variable to store the value
    value_found = False

    # Extract the desired text
    for p in soup.find_all('p'):
        text = p.get_text()
        if 'of coin at ' in text:
            value = text.split(' USD')[0].split()[8]
            print(value)
            value_found = True
            break  # Exit after finding the first match

    if not value_found:
        print("No relevant information found.")

except requests.exceptions.RequestException as e:
    print(f"Error fetching data: {e}")