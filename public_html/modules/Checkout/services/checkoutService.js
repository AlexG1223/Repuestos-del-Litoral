/**
 * Servicio de envío de orden de compra (Checkout).
 */

export async function submitOrder(customerData, cartItems) {
  const payload = {
    customerName: customerData.customerName,
    customerEmail: customerData.customerEmail,
    customerPhone: customerData.customerPhone,
    customerAddress: customerData.customerAddress,
    paymentMethod: customerData.paymentMethod,
    items: cartItems.map(item => ({
      productId: item.productId,
      quantity: item.quantity,
      name: item.name
    }))
  };

  const response = await fetch('/api/checkout.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  const data = await response.json();

  if (response.status === 422 && data.errors) {
    return { success: false, errors: data.errors };
  }

  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Error al procesar el pedido en el servidor.');
  }

  return { success: true, data: data.data };
}
