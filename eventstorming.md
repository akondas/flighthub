#FlightHub

AddFlight-> //flightId, number
FlightAdded //flightId, number

ReserveTicket-> //reservationId, userId, flightId, seat
TicketReserved //reservationId, userId, flightId, seat

ConfirmReservation-> //reservationId
ReservationConfirmed //reservationId

CancelReservation-> //reservationId
ReservationCanceled //reservationId

Flights*
Reservations*
Tickets*
Users*
