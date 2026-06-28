import React, { useState } from 'react';
import axios from 'axios';

const FeedbackForm = () => {
  const [formData, setFormData] = useState({ name: '', email: '', feedback: '', rating: 5 });
  const [status, setStatus] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setStatus('Sending...');

    try {
      // Assuming the backend API is at /feedback
      await axios.post('/feedback', {
        name: formData.name,
        email: formData.email,
        feedback: formData.feedback,
      });
      setStatus("Feedback sent successfully!");
      setFormData({ name: "", email: "", feedback: "", rating: 5 });
    } catch (error) {
      setStatus("Failed to send feedback. Please try again.");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="p-6 bg-white rounded-xl shadow-lg border border-gray-100 max-w-md mx-auto">
      <h2 className="text-xl font-bold mb-4">Customer Feedback</h2>
      <input type="text" placeholder="Name" value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} className="w-full p-2 mb-3 border rounded" required />
      <input type="email" placeholder="Email" value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} className="w-full p-2 mb-3 border rounded" required />
      <textarea placeholder="Your message" value={formData.feedback} onChange={(e) => setFormData({...formData, feedback: e.target.value})} className="w-full p-2 mb-3 border rounded" required />
      <button type="submit" disabled={isSubmitting} className="w-full bg-primary text-white p-2 rounded font-bold">
        {isSubmitting ? 'Sending...' : 'Submit Feedback'}
      </button>
      {status && <p className="mt-3 text-sm font-semibold">{status}</p>}
    </form>
  );
};

export default FeedbackForm;
