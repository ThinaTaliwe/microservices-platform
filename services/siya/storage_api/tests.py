from django.test import TestCase
from rest_framework.test import APIClient
from rest_framework import status 
from core_models.models.storage import Storage
from core_models.models.storage_has_item import StorageHasItem
from core_models.models.bu import Bu
from core_models.models.item import Item

class StorageApiTests(TestCase):
    def setUp(self):
        self.client = APIClient()
        self.bu = Bu.objects.create(name="Test BU")
        self.item = Item.objects.create(code="ITEM001", bu=self.bu)

    def test_create_storage_and_item_flow(self):
        """Test the sequential creation of storage and then an item."""
        storage_data = {
            "storage_start_time": "2026-02-10T10:00:00Z",
            "bu": self.bu.id,
            "storage_reference": "WH-001"
        }
        response = self.client.post('/api/storage/storages/', storage_data)
        
        self.assertEqual(response.status_code, status.HTTP_201_CREATED)
        storage_id = response.data['id']

        item_data = {
            "storage": storage_id,
            "item": self.item.id,
            "quantity": 50.00
        }
        item_res = self.client.post('/api/storage/storage-items/', item_data)
        self.assertEqual(item_res.status_code, status.HTTP_201_CREATED)
        self.assertEqual(StorageHasItem.objects.filter(storage_id=storage_id).count(), 1)

    def test_invalid_timestamp_logic(self):
        """Test that the serializer catches end_time occurring before start_time."""
        data = {
            "storage_start_time": "2026-02-10T10:00:00Z",
            "storage_end_time": "2026-01-01T10:00:00Z", 
            "bu": self.bu.id
        }
        
        response = self.client.post('/api/storage/storage/', data)
        self.assertEqual(response.status_code, status.HTTP_400_BAD_REQUEST)
        self.assertIn('storage_end_time', response.data)