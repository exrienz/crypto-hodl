import requests
from bs4 import BeautifulSoup
import sys

hodl_id = sys.argv[1]
host_address=sys.argv[2]

url = f'{host_address}/index.php?hodl_id={hodl_id}'
response = requests.get(url)

soup = BeautifulSoup(response.content, 'html.parser')

for p in soup.find_all('p'):
    text = p.get_text()
    if 'of coin at ' in text:
        value = text.split(' USD')[0].split()[8]
        print(value)
