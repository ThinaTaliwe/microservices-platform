from .searates_service_lg import searates_get_rates


def get_shipping_rates(origin, destination):

    try:
        rates = searates_get_rates(origin, destination)

        if rates:
            return {
                "provider": "searates",
                "data": rates
            }

    except Exception as e:
        print(f"SeaRates failed: {e}")

    return {"error": "No rates found"}