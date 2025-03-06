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

    # Extract and store all price values
    values = []
    
    for p in soup.find_all('p'):
        text = p.get_text()
        if 'of coin at ' in text and 'USD' in text:
            try:
                words = text.split()
                usd_index = words.index('USD')  # Find the index of "USD"
                value = words[usd_index - 1]  # The price is the word before "USD"
                values.append(value)
            except (ValueError, IndexError):
                continue  # Skip if parsing fails

    if values:
        print("\n".join(values))  # Print all extracted prices, one per line
    else:
        print("No relevant information found.")

except requests.exceptions.RequestException as e:
    print(f"Error fetching data: {e}")
