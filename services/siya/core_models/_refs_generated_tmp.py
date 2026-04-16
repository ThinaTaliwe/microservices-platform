# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#   * Rearrange models' order
#   * Make sure each model has one field with primary_key=True
#   * Make sure each ForeignKey and OneToOneField has `on_delete` set to the desired behavior
#   * Remove `managed = False` lines if you wish to allow Django to create, modify, and delete the table
# Feel free to rename the models, but don't rename db_table values or field names.
from django.db import models


class Address(models.Model):
    id = models.BigAutoField(primary_key=True)
    adress_type = models.ForeignKey('AdressType', models.DO_NOTHING)
    name = models.CharField(blank=True, null=True)
    line1 = models.CharField(blank=True, null=True)
    line2 = models.CharField(blank=True, null=True)
    line3 = models.CharField(blank=True, null=True)
    p_o_box = models.CharField(blank=True, null=True)
    suburb = models.CharField(blank=True, null=True)
    city = models.ForeignKey('City', models.DO_NOTHING, blank=True, null=True)
    city_0 = models.CharField(db_column='city', blank=True, null=True)  # Field renamed because of name conflict.
    zip = models.CharField(db_column='ZIP', blank=True, null=True)  # Field name made lowercase.
    district = models.CharField(blank=True, null=True)
    province = models.ForeignKey('Province', models.DO_NOTHING, blank=True, null=True)
    province_0 = models.CharField(db_column='province', blank=True, null=True)  # Field renamed because of name conflict.
    country = models.ForeignKey('Country', models.DO_NOTHING, blank=True, null=True)
    continent = models.CharField(blank=True, null=True)
    customer = models.ForeignKey('Customer', models.DO_NOTHING, blank=True, null=True)
    bu = models.ForeignKey('Bu', models.DO_NOTHING, blank=True, null=True)
    company = models.ForeignKey('Company', models.DO_NOTHING, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'address'


class Bu(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu_name = models.CharField()
    short_code = models.CharField(blank=True, null=True)
    sub_unit = models.IntegerField(blank=True, null=True)
    parent_bu = models.ForeignKey('self', models.DO_NOTHING, blank=True, null=True)
    company = models.ForeignKey('Company', models.DO_NOTHING)
    system = models.ForeignKey('System', models.DO_NOTHING)
    ops_currency = models.ForeignKey('Currency', models.DO_NOTHING)
    report_currency = models.ForeignKey('Currency', models.DO_NOTHING, related_name='bu_report_currency_set')
    country = models.ForeignKey('Country', models.DO_NOTHING)
    default_warehouse = models.ForeignKey('Warehouse', models.DO_NOTHING, blank=True, null=True)
    logo = models.CharField(blank=True, null=True)
    config_path = models.CharField(blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'bu'


class Uom(models.Model):
    name = models.CharField(blank=True, null=True)
    description = models.CharField(blank=True, null=True)
    symbol = models.CharField(blank=True, null=True)
    base_uom = models.ForeignKey('self', models.DO_NOTHING)
    base_uom_ratio = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'uom'


class HarmonisationCode(models.Model):
    code = models.CharField(blank=True, null=True)
    name = models.CharField(blank=True, null=True)
    description = models.TextField(blank=True, null=True)
    chapter = models.CharField(blank=True, null=True)
    version = models.CharField(blank=True, null=True)
    country = models.ForeignKey('Country', models.DO_NOTHING, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'harmonisation_code'


class InstructionType(models.Model):
    name = models.CharField(blank=True, null=True)
    description = models.CharField(blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'instruction_type'


class ModeOfTransport(models.Model):
    name = models.CharField(blank=True, null=True)
    description = models.CharField(blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'mode_of_transport'


class ShipmentTypeHasShipmentType(models.Model):
    id = models.BigAutoField(primary_key=True)
    parent_shipment_type = models.ForeignKey('ShipmentType', models.DO_NOTHING)
    child_shipment_type = models.ForeignKey('ShipmentType', models.DO_NOTHING, related_name='shipmenttypehasshipmenttype_child_shipment_type_set')
    name = models.CharField(blank=True, null=True)
    description = models.CharField(blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_type_has_shipment_type'


class Transactions(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey(Bu, models.DO_NOTHING)
    transaction_type = models.ForeignKey('TransactionType', models.DO_NOTHING)
    transaction_date = models.DateTimeField()
    transaction_qty = models.DecimalField(max_digits=20, decimal_places=5)
    uom = models.ForeignKey(Uom, models.DO_NOTHING, blank=True, null=True)
    business = models.ForeignKey('Business', models.DO_NOTHING, blank=True, null=True)
    customer = models.ForeignKey('Customer', models.DO_NOTHING, blank=True, null=True)
    supplier = models.ForeignKey('Supplier', models.DO_NOTHING, blank=True, null=True)
    item = models.ForeignKey('Item', models.DO_NOTHING, blank=True, null=True)
    from_item = models.ForeignKey('Item', models.DO_NOTHING, related_name='transactions_from_item_set', blank=True, null=True)
    from_site = models.ForeignKey('Site', models.DO_NOTHING, blank=True, null=True)
    from_warehouse = models.ForeignKey('Warehouse', models.DO_NOTHING, blank=True, null=True)
    from_location = models.ForeignKey('Location', models.DO_NOTHING, blank=True, null=True)
    from_batch = models.CharField(blank=True, null=True)
    to_item = models.ForeignKey('Item', models.DO_NOTHING, related_name='transactions_to_item_set', blank=True, null=True)
    to_site = models.ForeignKey('Site', models.DO_NOTHING, related_name='transactions_to_site_set', blank=True, null=True)
    to_warehouse = models.ForeignKey('Warehouse', models.DO_NOTHING, related_name='transactions_to_warehouse_set', blank=True, null=True)
    to_location = models.ForeignKey('Location', models.DO_NOTHING, related_name='transactions_to_location_set', blank=True, null=True)
    to_batch = models.CharField(blank=True, null=True)
    sales_order_line = models.ForeignKey('SalesOrderLine', models.DO_NOTHING, blank=True, null=True)
    purchase_order_line = models.ForeignKey('PurchaseOrderLine', models.DO_NOTHING, blank=True, null=True)
    production_order = models.ForeignKey('ProductionOrder', models.DO_NOTHING, blank=True, null=True)
    production_material = models.ForeignKey('ProductionMaterial', models.DO_NOTHING, blank=True, null=True)
    operation = models.ForeignKey('Operation', models.DO_NOTHING, blank=True, null=True)
    tool = models.ForeignKey('Tool', models.DO_NOTHING, blank=True, null=True)
    machine = models.ForeignKey('Machine', models.DO_NOTHING, blank=True, null=True)
    membership = models.ForeignKey('Membership', models.DO_NOTHING, blank=True, null=True)
    membership_fee_requests = models.ForeignKey('MembershipFeeRequests', models.DO_NOTHING, blank=True, null=True)
    membership_payment_receipts = models.ForeignKey('MembershipPaymentReceipts', models.DO_NOTHING, blank=True, null=True)
    payment_method = models.ForeignKey('PaymentMethod', models.DO_NOTHING, blank=True, null=True)
    transaction_local_value = models.DecimalField(max_digits=20, decimal_places=3, blank=True, null=True)
    transaction_local_currency = models.ForeignKey('Currency', models.DO_NOTHING, blank=True, null=True)
    transaction_bu_value = models.DecimalField(max_digits=20, decimal_places=3, blank=True, null=True)
    transaction_bu_currency = models.ForeignKey('Currency', models.DO_NOTHING, related_name='transactions_transaction_bu_currency_set', blank=True, null=True)
    transaction_value_exchange = models.DecimalField(max_digits=10, decimal_places=6, blank=True, null=True)
    transaction_year = models.IntegerField(blank=True, null=True)
    transaction_month_year = models.IntegerField(blank=True, null=True)
    transaction_description = models.CharField(blank=True, null=True)
    transaction_document_reference = models.CharField(blank=True, null=True)
    transaction_debit_value = models.DecimalField(max_digits=20, decimal_places=4, blank=True, null=True)
    transaction_debit_account_name = models.CharField(blank=True, null=True)
    transaction_credit_value = models.DecimalField(max_digits=20, decimal_places=4, blank=True, null=True)
    transaction_credit_account_name = models.CharField(blank=True, null=True)
    created_employee = models.ForeignKey('Employee', models.DO_NOTHING)
    last_updated_employee = models.ForeignKey('Employee', models.DO_NOTHING, related_name='transactions_last_updated_employee_set')
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'transactions'
