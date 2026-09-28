import axios from "./axios";

export const generatekhqr_api = async (order) => {
  const response = await axios.post("/api/payment/generate-qr", {
    order_id: order.id,
  });
  return response.data;
};

export const checkpayment_api = async (orderId) => {
  const response = await axios.post("/api/payment/verify", {
    order_id: orderId,
  });
  return response.data;
};

export const confirmManual_api = async (orderId) => {
  const response = await axios.post("/api/payment/confirm-manual", {
    order_id: orderId,
  });
  return response.data;
};