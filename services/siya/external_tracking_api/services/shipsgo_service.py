import requests
from django.conf import settings

BASE_URL = "https://api.shipsgo.com/v2"


def shipsgo_track_container(container):

    url = f"{BASE_URL}/ocean/shipments"

    headers = {
        "Content-Type": "application/json",
        "X-Shipsgo-User-Token": settings.SHIPSGO_TOKEN
    }

    payload = {
        "container_number": container
    }

    response = requests.post(url, json=payload, headers=headers)

    return response.json()