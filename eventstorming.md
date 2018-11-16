#FlightHub

AddFlight-> //id, number
FlightAdded //id

ReserveTicket-> //id, flightId, seat
TicketReserved //id

ConfirmReservation-> //id
ReservationConfirmed //id

CancelReservation-> //id
ReservationCanceled //id

Flights*
Reservations*
Tickets*
