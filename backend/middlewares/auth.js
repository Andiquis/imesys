import jwt from 'jsonwebtoken';
import dotenv from 'dotenv';

dotenv.config();

const secretKey = process.env.JWT_SECRET;

export function authMiddleware(req, res, next) {
  const authHeader = req.headers['authorization'];

  if (!authHeader) return res.status(401).json({ message: 'Token no proporcionado' });

  const token = authHeader.split(' ')[1]; // Se espera "Bearer <token>"

  if (!token) return res.status(401).json({ message: 'Token mal formado' });

  jwt.verify(token, secretKey, (err, user) => {
    if (err) return res.status(403).json({ message: 'Token inválido o expirado' });

    req.user = user; // Puedes usar esta info en tus rutas protegidas
    next();
  });
}
