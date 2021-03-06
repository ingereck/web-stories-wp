/*
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * External dependencies
 */
import styled from 'styled-components';
import { rgba } from 'polished';

const ColorBox = styled.div`
  height: 32px;
  width: 122px;
  color: ${({ theme }) => rgba(theme.colors.fg.v1, 0.86)} !important;
  background-color: ${({ theme }) => rgba(theme.colors.bg.v0, 0.3)} !important;
  border-radius: 4px;
  overflow: hidden;
  align-items: center;

  &:focus,
  & input:focus {
    outline: none;
    background: ${({ theme }) => theme.colors.fg.v1};
    color: ${({ theme }) => rgba(theme.colors.bg.v0, 0.55)};
  }
`;

export default ColorBox;
