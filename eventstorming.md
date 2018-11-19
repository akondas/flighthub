#FlightHub

AddFlight-> //flightId, number
FlightAdded //flightId, number

ReserveTicket-> //reservationId, userId, flightId, seat
TicketReserved //reservationId, userId, flightId, seat

ConfirmReservation-> //reservationId
ReservationConfirmed //reservationId

CancelReservation-> //reservationId
ReservationCanceled //reservationId

BlockSeat-> //flightId, seat
SeatBlocked //flightId, seat

RegisterCustomer-> //customerId, email
CustomerRegistered //customerId, email

AddCustomerAddress-> //customerId, addressId, street, city, postCode
CustomerAddressAdded //customerId, addressId, street, city, postCode

ChangeCustomerEmail-> //customerId, email
CustomerEmailChanged //customerId, oldEmail, newEmail


Flights*
Reservations*
Tickets*
Customers*
