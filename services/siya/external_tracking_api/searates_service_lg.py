import requests

SEARATES_URL = "https://rates.searates.com/graphql"
SEARATES_TOKEN = "YOUR_TOKEN_HERE"


def searates_get_rates(coordinates_from, coordinates_to, container="ST40", shipping_type="FCL"):

    query = """
    query Rates($shippingType: ShippingTypes!, $coordinatesFrom: [Float!]!, $coordinatesTo: [Float!]!, $container: ContainerTypes!, $date: Date!) {
      rates(
        shippingType: $shippingType
        coordinatesFrom: $coordinatesFrom
        coordinatesTo: $coordinatesTo
        container: $container
        date: $date
      ) {
        general {
          shipmentId
          totalPrice
          totalCurrency
          totalTransitTime
        }
        points {
          provider
          totalPrice
          totalCurrency
          location {
            name
            country
          }
        }
      }
    }
    """

    variables = {
        "shippingType": shipping_type,
        "coordinatesFrom": coordinates_from,
        "coordinatesTo": coordinates_to,
        "container": container,
        "date": "2026-05-20"
    }

    response = requests.post(
        SEARATES_URL,
        json={
            "query": query,
            "variables": variables
        },
        headers={
            "Authorization": f"Bearer {SEARATES_TOKEN}",
            "Content-Type": "application/json"
        }
    )

    data = response.json()

    return data.get("data", {}).get("rates", [])