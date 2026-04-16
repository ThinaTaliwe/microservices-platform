# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#   * Rearrange models' order
#   * Make sure each model has one field with primary_key=True
#   * Make sure each ForeignKey and OneToOneField has `on_delete` set to the desired behavior
#   * Remove `managed = False` lines if you wish to allow Django to create, modify, and delete the table
# Feel free to rename the models, but don't rename db_table values or field names.
from django.db import models


class Shipment(models.Model):
    id = models.BigAutoField(primary_key=True)
    shipment_type = models.ForeignKey('ShipmentType', models.DO_NOTHING)
    mode_of_transport = models.ForeignKey('ModeOfTransport', models.DO_NOTHING, blank=True, null=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    shipment_instruction = models.ForeignKey('ShipmentInstruction', models.DO_NOTHING)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    loading = models.ForeignKey('Loading', models.DO_NOTHING)
    loading_started = models.DateTimeField(blank=True, null=True)
    loading_ended = models.DateTimeField(blank=True, null=True)
    movement = models.ForeignKey('Movement', models.DO_NOTHING)
    movement_started = models.DateTimeField(blank=True, null=True)
    movement_ended = models.DateTimeField(blank=True, null=True)
    offloading = models.ForeignKey('Offloading', models.DO_NOTHING)
    offloading_started = models.DateTimeField(blank=True, null=True)
    offloading_ended = models.DateTimeField(blank=True, null=True)
    storage = models.ForeignKey('Storage', models.DO_NOTHING)
    storage_started = models.DateTimeField(blank=True, null=True)
    storage_ended = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment'
        db_table_comment = 'This is the movement of any single type that can happen.  The types can include a human.'


class ShipmentType(models.Model):
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_type'
        db_table_comment = 'These are any types that moves a physical object.  This is not designed for virtual objects.'


class ShipmentHasShipment(models.Model):
    parent_shipment = models.ForeignKey(Shipment, models.DO_NOTHING)
    child_shipment = models.ForeignKey(Shipment, models.DO_NOTHING, related_name='shipmenthasshipment_child_shipment_set')
    id = models.BigAutoField(primary_key=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    code = models.CharField(max_length=45, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_has_shipment'


class ShipmentHasPreviousShipments(models.Model):
    id = models.BigAutoField(primary_key=True)
    shipment = models.ForeignKey(Shipment, models.DO_NOTHING)
    previous_shipment = models.ForeignKey(Shipment, models.DO_NOTHING, related_name='shipmenthaspreviousshipments_previous_shipment_set')
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_has_previous_shipments'


class ShipmentInstruction(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    instruction_type = models.ForeignKey('InstructionType', models.DO_NOTHING)
    instruction_reference = models.CharField(max_length=45, blank=True, null=True)
    instruction_detail = models.CharField(max_length=255, blank=True, null=True)
    from_address = models.ForeignKey('Address', models.DO_NOTHING, blank=True, null=True)
    to_address = models.ForeignKey('Address', models.DO_NOTHING, related_name='shipmentinstruction_to_address_set', blank=True, null=True)
    from_warehouse = models.ForeignKey('Warehouse', models.DO_NOTHING, blank=True, null=True)
    to_warehouse = models.ForeignKey('Warehouse', models.DO_NOTHING, related_name='shipmentinstruction_to_warehouse_set', blank=True, null=True)
    from_location = models.ForeignKey('Location', models.DO_NOTHING, blank=True, null=True)
    to_location = models.ForeignKey('Location', models.DO_NOTHING, related_name='shipmentinstruction_to_location_set', blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_instruction'


class ShipmentInstructionHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    shipment_instruction = models.ForeignKey(ShipmentInstruction, models.DO_NOTHING)
    item = models.ForeignKey('Item', models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_instruction_has_item'


class ShipmentInstructionHasShipmentInstruction(models.Model):
    id = models.BigAutoField(primary_key=True)
    shipment_instruction = models.ForeignKey(ShipmentInstruction, models.DO_NOTHING)
    shipment_instruction_id1 = models.ForeignKey(ShipmentInstruction, models.DO_NOTHING, db_column='shipment_instruction_id1', related_name='shipmentinstructionhasshipmentinstruction_shipment_instruction_id1_set')
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_instruction_has_shipment_instruction'


class ShipmentItems(models.Model):
    id = models.BigAutoField(primary_key=True)
    shipment = models.ForeignKey(Shipment, models.DO_NOTHING)
    item = models.ForeignKey('Item', models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_items'


class Loading(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    loading_type = models.ForeignKey('LoadingType', models.DO_NOTHING)
    parent_loading = models.ForeignKey('self', models.DO_NOTHING, blank=True, null=True)
    loading_start_time = models.DateTimeField(blank=True, null=True)
    loading_end_time = models.DateTimeField(blank=True, null=True)
    loading_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'loading'


class LoadingType(models.Model):
    id = models.IntegerField(primary_key=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'loading_type'


class LoadingHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    loading = models.ForeignKey(Loading, models.DO_NOTHING)
    item = models.ForeignKey('Item', models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'loading_has_item'


class Movement(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    parent_movement = models.ForeignKey('self', models.DO_NOTHING, blank=True, null=True)
    movement_start_time = models.DateTimeField(blank=True, null=True)
    movement_end_time = models.DateTimeField(blank=True, null=True)
    movement_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'movement'


class MovementHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    movement = models.ForeignKey(Movement, models.DO_NOTHING)
    item = models.ForeignKey('Item', models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'movement_has_item'


class Offloading(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    loading_type = models.ForeignKey(LoadingType, models.DO_NOTHING)
    parent_offloading = models.ForeignKey('self', models.DO_NOTHING, blank=True, null=True)
    offloading_start_time = models.DateTimeField(blank=True, null=True)
    offloading_end_time = models.DateTimeField(blank=True, null=True)
    offloading_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'offloading'


class OffloadingHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    offloading = models.ForeignKey(Offloading, models.DO_NOTHING)
    item = models.ForeignKey('Item', models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'offloading_has_item'


class Storage(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    parent_storage = models.ForeignKey('self', models.DO_NOTHING, blank=True, null=True)
    storage_start_time = models.DateTimeField(blank=True, null=True)
    storage_end_time = models.DateTimeField(blank=True, null=True)
    storage_refence = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'storage'


class StorageHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    storage = models.ForeignKey(Storage, models.DO_NOTHING)
    item = models.ForeignKey('Item', models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'storage_has_item'


class Item(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    code = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=45, blank=True, null=True)
    long_description = models.CharField(max_length=255, blank=True, null=True)
    stocked = models.IntegerField(db_comment='0 = Non-stock item code\n1 = stocked item code\nNon-stock also include services and virtual goods')
    active = models.IntegerField(db_comment='0 = no, 1= yes')
    harmonisation_code = models.ForeignKey('HarmonisationCode', models.DO_NOTHING, blank=True, null=True)
    picture = models.CharField(max_length=255, blank=True, null=True)
    total_inventory = models.DecimalField(max_digits=20, decimal_places=5, blank=True, null=True, db_comment='total inventory in base_uom')
    base_uom = models.ForeignKey('Uom', models.DO_NOTHING, blank=True, null=True, db_comment='Stocking unit of measure for the item')
    std_cost = models.DecimalField(max_digits=20, decimal_places=5, blank=True, null=True, db_comment='per base UOM in currency')
    forecasted = models.IntegerField(blank=True, null=True, db_comment='0 = no, 1= yes')
    forecast_batch = models.IntegerField(blank=True, null=True)
    bom_batch = models.DecimalField(max_digits=20, decimal_places=5, blank=True, null=True)
    prev_item = models.ForeignKey('self', models.DO_NOTHING, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'item'


class Inventory(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    country = models.ForeignKey('Country', models.DO_NOTHING)
    site = models.ForeignKey('Site', models.DO_NOTHING, blank=True, null=True)
    warehouse = models.ForeignKey('Warehouse', models.DO_NOTHING, blank=True, null=True)
    location = models.ForeignKey('Location', models.DO_NOTHING, blank=True, null=True)
    item = models.ForeignKey(Item, models.DO_NOTHING)
    sku = models.ForeignKey('Sku', models.DO_NOTHING, blank=True, null=True)
    sku_code = models.CharField(max_length=255, blank=True, null=True)
    qty = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    uom = models.ForeignKey('Uom', models.DO_NOTHING)
    batch = models.CharField(max_length=45, blank=True, null=True)
    standard_cost = models.DecimalField(max_digits=20, decimal_places=5, blank=True, null=True)
    open_qty_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    open_qty_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    open_qty_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    open_qty_fin_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    open_qty_fin_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    open_qty_fin_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    sales_qty_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    sales_qty_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    sales_qty_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    sales_qty_fin_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    sales_qty_fin_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    sales_qty_fin_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    purchase_qty_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    purchase_qty_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    purchase_qty_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    purchase_qty_fin_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    purchase_qty_fin_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    purchase_qty_fin_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    production_qty_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    production_qty_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    production_qty_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    production_qty_fin_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    production_qty_fin_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    production_qty_fin_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    adjustment_qty_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    adjustment_qty_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    adjustment_qty_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    adjustment_qty_fin_month = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    adjustment_qty_fin_quarter = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    adjustment_qty_fin_year = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    last_transactions = models.ForeignKey('Transactions', models.DO_NOTHING)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'inventory'
        db_table_comment = 'All the inventory values are stored of physical goods transferred'


class Warehouse(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    site = models.ForeignKey('Site', models.DO_NOTHING, blank=True, null=True)
    short_code = models.CharField(max_length=5, blank=True, null=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    address = models.ForeignKey('Address', models.DO_NOTHING)
    intransit_warehouse = models.IntegerField(blank=True, null=True, db_comment='1 - in-trasit, 0 - physical warehouse')
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'warehouse'


class Location(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING)
    warehouse = models.ForeignKey(Warehouse, models.DO_NOTHING)
    short_code = models.CharField(max_length=10, blank=True, null=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'location'
