#
# Copyright (C) 2012 Cyril Bouthors <cyril@bouthors.org>
#
# This program is free software: you can redistribute it and/or modify it under
# the terms of the GNU General Public License as published by the Free Software
# Foundation, either version 3 of the License, or (at your option) any later
# version.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along with
# this program. If not, see <http://www.gnu.org/licenses/>.

APXS=apxs2

all: mod_cloudflare.so

%.so: %.c
	$(APXS) -c -o $@ $<

install: mod_cloudflare.so
	$(APXS) -i -a -n mod_cloudflare mod_cloudflare.so

clean:
	$(RM) -r *~ *.o *.so *.lo *.la *.slo .libs
