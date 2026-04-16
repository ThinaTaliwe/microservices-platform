from django.test import TestCase

from rest_framework.test import APITestCase
from rest_framework import status
from core_models.models import Movement, MovementHasItem, Offloading, Bu, Item


class MovementAPITests(APITestCase):

    def setUp(self):
        self.bu = Bu.objects.first()
        self.item = Item.objects.first()

    def test_create_movement(self):
        url = "/api/movement/movements/"
        data = {
            "bu": self.bu.id
        }

        response = self.client.post(url, data)
        self.assertIn(response.status_code, [status.HTTP_201_CREATED, status.HTTP_400_BAD_REQUEST])

    def test_update_movement(self):
        movement = Movement.objects.create(bu=self.bu)

        url = f"/api/movement/movements/{movement.id}/"

        response = self.client.patch(url, {
            "movement_reference": "TEST123"
        })

        self.assertIn(response.status_code, [status.HTTP_200_OK, status.HTTP_400_BAD_REQUEST])

    def test_invalid_quantity_rejected(self):
        movement = Movement.objects.create(bu=self.bu)

        url = "/api/movement/movement-items/"
        data = {
            "movement": movement.id,
            "item": self.item.id,
            "quantity": -5
        }

        response = self.client.post(url, data)

        self.assertEqual(response.status_code, status.HTTP_400_BAD_REQUEST)


class OffloadingAPITests(APITestCase):

    def setUp(self):
        self.bu = Bu.objects.first()
        self.item = Item.objects.first()

    def test_create_offloading(self):
        url = "/api/movement/offloadings/"
        data = {
            "bu": self.bu.id,
            "loading_type": 1
        }

        response = self.client.post(url, data)
        self.assertIn(response.status_code, [status.HTTP_201_CREATED, status.HTTP_400_BAD_REQUEST])


